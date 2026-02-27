<!DOCTYPE html>
<html>
<head>
    <title>Тест автоопределения языка</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { background: #f5f5f5; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🌍 Тест автоопределения языка по IP</h1>
    
    <div class="card">
        <h3>Текущая локаль: <strong>{{ app()->getLocale() }}</strong></h3>
        <p>Поддерживаемые языки: nl (голландский), fr (французский)</p>
    </div>

    <div class="card">
        <h3>🧪 Тестирование с разными IP</h3>
        <button onclick="testWithIP('95.96.45.123', 'nl')">🇳🇱 Голландский IP</button>
        <button onclick="testWithIP('176.31.123.45', 'fr')">🇫🇷 Французский IP</button>
        <button onclick="testWithIP('193.191.168.123', 'nl')">🇧🇪 Бельгийский IP</button>
        <button onclick="testWithIP('127.0.0.1', 'nl')">🏠 Localhost</button>
    </div>

    <div class="card">
        <h3>📊 Результаты тестов</h3>
        <div id="test-results">Нажмите на кнопки выше для тестирования...</div>
    </div>

    <div class="card">
        <h3>🔍 Текущая информация о геолокации</h3>
        <div id="current-info">Загрузка...</div>
    </div>

    <script>
        async function testWithIP(ip, expectedLocale) {
            const resultsDiv = document.getElementById('test-results');
            resultsDiv.innerHTML = '<p>Тестируем с IP: ' + ip + '...</p>';
            
            try {
                const response = await fetch('/debug-locale', {
                    headers: {
                        'CF-Connecting-IP': ip
                    }
                });
                const data = await response.json();
                
                const isCorrect = data.current_locale === expectedLocale;
                const statusClass = isCorrect ? 'success' : 'warning';
                
                resultsDiv.innerHTML = `
                    <div class="card ${statusClass}">
                        <h4>Тест с IP: ${ip}</h4>
                        <p><strong>Ожидаемый язык:</strong> ${expectedLocale}</p>
                        <p><strong>Определенный язык:</strong> ${data.current_locale}</p>
                        <p><strong>Результат:</strong> ${isCorrect ? '✅ Верно' : '❌ Неверно'}</p>
                        <p><strong>Страна:</strong> ${data.location ? data.location.country + ' (' + data.location.country_code + ')' : 'Не определена'}</p>
                        <p><strong>Город:</strong> ${data.location ? data.location.city : 'Не определен'}</p>
                        <details>
                            <summary>Полный JSON ответ</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                    </div>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="card warning"><p>❌ Ошибка: ${error.message}</p></div>`;
            }
        }
        
        async function loadCurrentInfo() {
            const infoDiv = document.getElementById('current-info');
            
            try {
                const response = await fetch('/debug-locale');
                const data = await response.json();
                
                infoDiv.innerHTML = `
                    <p><strong>Текущий IP:</strong> ${data.client_ip}</p>
                    <p><strong>Текущая локаль:</strong> ${data.current_locale}</p>
                    <p><strong>Страна:</strong> ${data.location ? data.location.country + ' (' + data.location.country_code + ')' : 'Не определена'}</p>
                    <p><strong>Город:</strong> ${data.location ? data.location.city : 'Не определен'}</p>
                    <p><strong>Языки браузера:</strong> ${data.browser_languages.length > 0 ? data.browser_languages.join(', ') : 'Не определены'}</p>
                    <details>
                        <summary>Заголовки запроса</summary>
                        <pre>${JSON.stringify(data.headers, null, 2)}</pre>
                    </details>
                `;
            } catch (error) {
                infoDiv.innerHTML = `<p>❌ Ошибка загрузки: ${error.message}</p>`;
            }
        }
        
        // Загрузка текущей информации при загрузке страницы
        loadCurrentInfo();
    </script>
</body>
</html>

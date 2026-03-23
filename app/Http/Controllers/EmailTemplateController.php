<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Services\PulsifyMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    /**
     * @var array<string, array{category: string, icon: string, description: string, variable_descriptions: array<string, string>}>
     */
    private const DEFINITIONS = [
        'welcome' => [
            'category' => 'Auth',
            'icon' => 'bi bi-stars',
            'description' => 'Welcome email sent to new users.',
            'variable_descriptions' => ['name' => 'Recipient name', 'company' => 'Company name', 'login_url' => 'Login page URL'],
        ],
        'otp_verification' => [
            'category' => 'Auth',
            'icon' => 'bi bi-shield-lock',
            'description' => 'OTP code email for verification.',
            'variable_descriptions' => ['name' => 'Recipient name', 'otp' => 'Verification code', 'expiry_minutes' => 'Code expiry in minutes'],
        ],
        'password_reset' => [
            'category' => 'Auth',
            'icon' => 'bi bi-key',
            'description' => 'Password reset email.',
            'variable_descriptions' => ['name' => 'Recipient name', 'reset_url' => 'Reset password link', 'expiry_minutes' => 'Link expiry in minutes'],
        ],
        'team_invite' => [
            'category' => 'Team',
            'icon' => 'bi bi-person-plus',
            'description' => 'Team invitation email.',
            'variable_descriptions' => ['inviter_name' => 'Inviter full name', 'company' => 'Company name', 'role' => 'Assigned role', 'invite_url' => 'Invite acceptance URL', 'expiry_days' => 'Invite expiry in days'],
        ],
        'invite_accepted' => [
            'category' => 'Team',
            'icon' => 'bi bi-check2-circle',
            'description' => 'Sent when an invited user joins.',
            'variable_descriptions' => ['new_member_name' => 'Accepted user name', 'new_member_email' => 'Accepted user email', 'role' => 'Assigned role', 'company' => 'Company name', 'team_url' => 'Team page URL'],
        ],
        'post_submitted' => [
            'category' => 'Content',
            'icon' => 'bi bi-send-check',
            'description' => 'Sent to reviewers when a post is submitted.',
            'variable_descriptions' => ['submitter_name' => 'Submitting user', 'post_title' => 'Post title', 'post_type' => 'Post type', 'channel' => 'Target channel', 'review_url' => 'Review URL'],
        ],
        'post_approved' => [
            'category' => 'Content',
            'icon' => 'bi bi-patch-check',
            'description' => 'Sent when a post is approved.',
            'variable_descriptions' => ['approver_name' => 'Approver name', 'post_title' => 'Post title', 'comment' => 'Approval note', 'post_url' => 'Post URL'],
        ],
        'post_rejected' => [
            'category' => 'Content',
            'icon' => 'bi bi-x-circle',
            'description' => 'Sent when revisions are required.',
            'variable_descriptions' => ['approver_name' => 'Reviewer name', 'post_title' => 'Post title', 'reason' => 'Rejection reason', 'post_url' => 'Post URL'],
        ],
        'task_assigned' => [
            'category' => 'Task',
            'icon' => 'bi bi-list-check',
            'description' => 'Sent when a task is assigned.',
            'variable_descriptions' => ['assigner_name' => 'Person assigning task', 'task_title' => 'Task title', 'task_type' => 'Task type', 'due_date' => 'Due date', 'priority' => 'Task priority', 'task_url' => 'Task URL'],
        ],
    ];

    public function index(Request $request): View
    {
        $this->ensureOwnerOrAdmin($request);

        $companyId = (int) $request->user()->company_id;
        $companyTemplates = EmailTemplate::query()
            ->where('company_id', $companyId)
            ->whereIn('template_key', array_keys(self::DEFINITIONS))
            ->get()
            ->keyBy('template_key');

        $defaultTemplates = EmailTemplate::query()
            ->whereNull('company_id')
            ->whereIn('template_key', array_keys(self::DEFINITIONS))
            ->get()
            ->keyBy('template_key');

        $categories = ['Auth', 'Team', 'Content', 'Task'];
        $grouped = [];

        foreach ($categories as $category) {
            $grouped[$category] = [];
        }

        foreach (self::DEFINITIONS as $key => $meta) {
            $companyTemplate = $companyTemplates->get($key);
            $defaultTemplate = $defaultTemplates->get($key);
            $template = $companyTemplate ?? $defaultTemplate;

            if (! $template) {
                continue;
            }

            $grouped[$meta['category']][] = [
                'key' => $key,
                'name' => $template->name,
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'is_custom' => $companyTemplate !== null,
            ];
        }

        return view('email-templates.index', [
            'grouped' => $grouped,
        ]);
    }

    public function edit(Request $request, string $key): View
    {
        $this->ensureOwnerOrAdmin($request);
        $this->assertKnownTemplateKey($key);

        $companyId = (int) $request->user()->company_id;
        $companyTemplate = EmailTemplate::query()->where('company_id', $companyId)->where('template_key', $key)->first();
        $activeTemplate = $companyTemplate ?? EmailTemplate::query()->whereNull('company_id')->where('template_key', $key)->firstOrFail();

        return view('email-templates.edit', [
            'templateKey' => $key,
            'template' => $activeTemplate,
            'isCustom' => $companyTemplate !== null,
            'variableDescriptions' => self::DEFINITIONS[$key]['variable_descriptions'],
        ]);
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $this->ensureOwner($request);
        $this->assertKnownTemplateKey($key);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:500'],
            'body_html' => ['required', 'string'],
        ]);

        $default = EmailTemplate::query()->whereNull('company_id')->where('template_key', $key)->firstOrFail();

        EmailTemplate::query()->updateOrCreate(
            ['company_id' => (int) $request->user()->company_id, 'template_key' => $key],
            [
                'name' => $default->name,
                'subject' => $validated['subject'],
                'body_html' => $validated['body_html'],
                'variables' => $default->variables,
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Template saved.');
    }

    public function reset(Request $request, string $key): RedirectResponse
    {
        $this->ensureOwner($request);
        $this->assertKnownTemplateKey($key);

        EmailTemplate::query()
            ->where('company_id', (int) $request->user()->company_id)
            ->where('template_key', $key)
            ->delete();

        return back()->with('success', 'Template reset to default.');
    }

    public function preview(Request $request, string $key): View
    {
        $this->ensureOwnerOrAdmin($request);
        $this->assertKnownTemplateKey($key);

        $template = EmailTemplate::getForCompany((int) $request->user()->company_id, $key);
        abort_if($template === null, 404);

        $previewSubject = $request->query('subject');
        $previewBody = $request->query('body_html');
        $subject = is_string($previewSubject) ? $previewSubject : (string) $template->subject;
        $body = is_string($previewBody) ? $previewBody : (string) $template->body_html;

        $sampleVars = [];
        foreach ((array) $template->variables as $var) {
            $sampleVars[(string) $var] = Str::headline((string) $var);
        }

        foreach ($sampleVars as $var => $value) {
            $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $subject = str_replace(['{{'.$var.'}}', '{{ '.$var.' }}'], $safeValue, $subject);
            $body = str_replace(['{{'.$var.'}}', '{{ '.$var.' }}'], $safeValue, $body);
        }

        return view('email-templates.preview', [
            'subject' => $subject,
            'body' => $body,
        ]);
    }

    public function sendTest(Request $request, string $key): JsonResponse
    {
        $this->ensureOwner($request);
        $this->assertKnownTemplateKey($key);

        $template = EmailTemplate::getForCompany((int) $request->user()->company_id, $key);
        if (! $template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        $sampleVars = [];
        foreach ((array) $template->variables as $var) {
            $sampleVars[(string) $var] = Str::headline((string) $var);
        }
        $sampleVars['company'] = (string) ($request->user()->company?->name ?? 'Your Company');
        $sampleVars['login_url'] = url('/login');
        $sampleVars['invite_url'] = url('/invite/accept?token=sample');
        $sampleVars['review_url'] = url('/content');
        $sampleVars['post_url'] = url('/content');
        $sampleVars['task_url'] = url('/tasks');
        $sampleVars['team_url'] = url('/team');
        $sampleVars['reset_url'] = url('/reset-password?token=sample&email='.urlencode((string) $request->user()->email));

        $mailer = new PulsifyMailer($request->user()->company);
        $ok = $mailer->send(
            $key,
            (string) $request->user()->email,
            (string) ($request->user()->name ?? 'User'),
            $sampleVars
        );

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Test email sent to '.$request->user()->email : 'Failed to send test email.',
        ], $ok ? 200 : 422);
    }

    private function ensureOwnerOrAdmin(Request $request): void
    {
        $role = strtolower((string) ($request->user()->role ?? ''));
        if (! in_array($role, ['owner', 'admin'], true)) {
            abort(403);
        }
    }

    private function ensureOwner(Request $request): void
    {
        if (strtolower((string) ($request->user()->role ?? '')) !== 'owner') {
            abort(403);
        }
    }

    private function assertKnownTemplateKey(string $key): void
    {
        abort_unless(array_key_exists($key, self::DEFINITIONS), 404);
    }
}

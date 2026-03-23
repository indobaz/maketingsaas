<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $header = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pulsify Email</title>
</head>
<body style="margin:0; padding:0; background:#ffffff; font-family: Arial, Helvetica, sans-serif; color:#272B41;">
  <div style="width:100%; padding:32px 16px;">
    <div style="max-width:600px; margin:0 auto; border:1px solid #EEF0F6; border-radius:12px; overflow:hidden;">
      <div style="padding:24px 24px 0 24px;">
        <div style="font-size:22px; font-weight:700; color:#5F63F2; letter-spacing:.2px;">Pulsify</div>
      </div>
      <div style="padding:20px 24px 28px 24px;">
HTML;

        $footer = <<<'HTML'
      </div>
      <div style="padding:16px 24px; background:#FAFBFD; border-top:1px solid #EEF0F6; font-size:12px; line-height:18px; color:#7b82a0;">
        This is an automated email from Pulsify.
      </div>
    </div>
  </div>
</body>
</html>
HTML;

        $templates = [
            [
                'template_key' => 'welcome',
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{company}} on Pulsify!',
                'variables' => ['name', 'company', 'login_url'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">Hi {{name}},</div>
<div style="font-size:14px; line-height:22px; margin-bottom:16px;">Welcome to <strong>{{company}}</strong> on Pulsify. We are excited to have you onboard.</div>
<div style="margin: 18px 0; text-align:center;">
  <a href="{{login_url}}" style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">Go to Login</a>
</div>
'.$footer,
            ],
            [
                'template_key' => 'otp_verification',
                'name' => 'Email Verification',
                'subject' => 'Your Pulsify verification code',
                'variables' => ['name', 'otp', 'expiry_minutes'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">Hi {{name}},</div>
<div style="font-size:14px; line-height:22px; margin-bottom:14px;">Your verification code is:</div>
<div style="background:#F4F5F7; border-radius:10px; padding:18px; text-align:center; margin:12px 0 10px 0;">
  <div style="font-size:32px; font-weight:800; letter-spacing:6px; color:#272B41;">{{otp}}</div>
</div>
<div style="font-size:13px; line-height:20px; color:#5b627a; margin-top:8px;">This code expires in {{expiry_minutes}} minutes.</div>
'.$footer,
            ],
            [
                'template_key' => 'team_invite',
                'name' => 'Team Invitation',
                'subject' => "You've been invited to join {{company}} on Pulsify",
                'variables' => ['inviter_name', 'company', 'role', 'invite_url', 'expiry_days'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">Hi there,</div>
<div style="font-size:14px; line-height:22px; margin-bottom:12px;"><strong>{{inviter_name}}</strong> invited you to join <strong>{{company}}</strong> on Pulsify.</div>
<div style="font-size:14px; line-height:22px; margin-bottom:18px;">Role assigned: <strong>{{role}}</strong></div>
<div style="margin: 18px 0 18px 0; text-align:center;">
  <a href="{{invite_url}}" style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">Accept Invitation</a>
</div>
<div style="font-size:13px; line-height:20px; color:#5b627a; margin-top:8px;">This invite expires in {{expiry_days}} days.</div>
'.$footer,
            ],
            [
                'template_key' => 'invite_accepted',
                'name' => 'Invite Accepted Notification',
                'subject' => '{{new_member_name}} joined your Pulsify workspace',
                'variables' => ['new_member_name', 'new_member_email', 'role', 'company', 'team_url'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">Great news!</div>
<div style="font-size:14px; line-height:22px; margin-bottom:12px;"><strong>{{new_member_name}}</strong> ({{new_member_email}}) accepted the invite and joined <strong>{{company}}</strong>.</div>
<div style="font-size:14px; line-height:22px; margin-bottom:18px;">Role: <strong>{{role}}</strong></div>
<div style="margin: 18px 0; text-align:center;">
  <a href="{{team_url}}" style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">Open Team</a>
</div>
'.$footer,
            ],
            [
                'template_key' => 'password_reset',
                'name' => 'Password Reset',
                'subject' => 'Reset your Pulsify password',
                'variables' => ['name', 'reset_url', 'expiry_minutes'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">Hi {{name}},</div>
<div style="font-size:14px; line-height:22px; margin-bottom:18px;">We received a request to reset your Pulsify password.</div>
<div style="margin: 18px 0; text-align:center;">
  <a href="{{reset_url}}" style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">Reset Password</a>
</div>
<div style="font-size:13px; line-height:20px; color:#5b627a; margin-top:8px;">This link expires in {{expiry_minutes}} minutes.</div>
'.$footer,
            ],
            [
                'template_key' => 'post_submitted',
                'name' => 'Post Submitted for Review',
                'subject' => 'New post ready for review: {{post_title}}',
                'variables' => ['submitter_name', 'post_title', 'post_type', 'channel', 'review_url'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">A post was submitted for review.</div>
<div style="font-size:14px; line-height:22px;">Submitter: <strong>{{submitter_name}}</strong></div>
<div style="font-size:14px; line-height:22px;">Title: <strong>{{post_title}}</strong></div>
<div style="font-size:14px; line-height:22px;">Type: <strong>{{post_type}}</strong></div>
<div style="font-size:14px; line-height:22px; margin-bottom:18px;">Channel: <strong>{{channel}}</strong></div>
<div style="margin: 18px 0; text-align:center;">
  <a href="{{review_url}}" style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">Review Post</a>
</div>
'.$footer,
            ],
            [
                'template_key' => 'post_approved',
                'name' => 'Post Approved',
                'subject' => "Your post '{{post_title}}' has been approved",
                'variables' => ['approver_name', 'post_title', 'comment', 'post_url'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">Good news, your post was approved.</div>
<div style="font-size:14px; line-height:22px;">Approver: <strong>{{approver_name}}</strong></div>
<div style="font-size:14px; line-height:22px;">Post: <strong>{{post_title}}</strong></div>
<div style="font-size:14px; line-height:22px; margin-bottom:18px;">Comment: {{comment}}</div>
<div style="margin: 18px 0; text-align:center;">
  <a href="{{post_url}}" style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">Open Post</a>
</div>
'.$footer,
            ],
            [
                'template_key' => 'post_rejected',
                'name' => 'Post Needs Revision',
                'subject' => "Your post '{{post_title}}' needs revision",
                'variables' => ['approver_name', 'post_title', 'reason', 'post_url'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">Your post needs revision.</div>
<div style="font-size:14px; line-height:22px;">Reviewer: <strong>{{approver_name}}</strong></div>
<div style="font-size:14px; line-height:22px;">Post: <strong>{{post_title}}</strong></div>
<div style="font-size:14px; line-height:22px; margin-bottom:18px;">Reason: {{reason}}</div>
<div style="margin: 18px 0; text-align:center;">
  <a href="{{post_url}}" style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">Open Post</a>
</div>
'.$footer,
            ],
            [
                'template_key' => 'task_assigned',
                'name' => 'Task Assigned',
                'subject' => 'New task assigned to you: {{task_title}}',
                'variables' => ['assigner_name', 'task_title', 'task_type', 'due_date', 'priority', 'task_url'],
                'body_html' => $header.'
<div style="font-size:16px; line-height:24px; margin-bottom:14px;">You have a new task assignment.</div>
<div style="font-size:14px; line-height:22px;">Assigner: <strong>{{assigner_name}}</strong></div>
<div style="font-size:14px; line-height:22px;">Task: <strong>{{task_title}}</strong></div>
<div style="font-size:14px; line-height:22px;">Type: <strong>{{task_type}}</strong></div>
<div style="font-size:14px; line-height:22px;">Due date: <strong>{{due_date}}</strong></div>
<div style="font-size:14px; line-height:22px; margin-bottom:18px;">Priority: <strong>{{priority}}</strong></div>
<div style="margin: 18px 0; text-align:center;">
  <a href="{{task_url}}" style="display:inline-block; background:#5F63F2; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;">Open Task</a>
</div>
'.$footer,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::query()->updateOrCreate(
                [
                    'company_id' => null,
                    'template_key' => $template['template_key'],
                ],
                [
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body_html' => $template['body_html'],
                    'variables' => $template['variables'],
                    'is_active' => true,
                ]
            );
        }
    }
}

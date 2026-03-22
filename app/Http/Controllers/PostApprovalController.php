<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PostApprovalController extends Controller
{
    public function submitForReview(Post $post): RedirectResponse
    {
        $this->assertPostInCompany($post);
        $user = Auth::user();

        if (! in_array($post->status, ['draft', 'rejected'], true)) {
            abort(403);
        }

        if (! $this->userIsCreatorAdminOrOwner($post, $user)) {
            abort(403);
        }

        $post->update(['status' => 'in_review']);

        PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => 'Submitted for review',
            'status_change' => 'in_review',
        ]);

        $this->notifyAdminsAndOwnersNewReview($post);

        return redirect()->route('content.edit', $post)->with('success', 'Post submitted for review');
    }

    public function approve(Post $post, Request $request): RedirectResponse
    {
        $this->assertPostInCompany($post);
        $user = Auth::user();

        if (! in_array($user->role, ['admin', 'owner'], true)) {
            abort(403);
        }

        if ($post->status !== 'in_review') {
            abort(403);
        }

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $post->update(['status' => 'approved']);

        $body = trim((string) ($validated['comment'] ?? ''));
        $commentText = $body !== '' ? $body : 'Approved';

        PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => $commentText,
            'status_change' => 'approved',
        ]);

        $this->notifyCreatorApproved($post);

        return redirect()->route('content.edit', $post)->with('success', 'Post approved');
    }

    public function reject(Post $post, Request $request): RedirectResponse
    {
        $this->assertPostInCompany($post);
        $user = Auth::user();

        if (! in_array($user->role, ['admin', 'owner'], true)) {
            abort(403);
        }

        if ($post->status !== 'in_review') {
            abort(403);
        }

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:500'],
        ]);

        $reason = $validated['comment'];

        $post->update(['status' => 'rejected']);

        PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => $reason,
            'status_change' => 'rejected',
        ]);

        $this->notifyCreatorRejected($post, $reason);

        return redirect()->route('content.edit', $post)->with('success', 'Post sent back for revision');
    }

    public function publishPost(Post $post): RedirectResponse
    {
        $this->assertPostInCompany($post);
        $user = Auth::user();

        if (! in_array($user->role, ['admin', 'owner'], true)) {
            abort(403);
        }

        if ($post->status !== 'approved') {
            abort(403);
        }

        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => 'Published',
            'status_change' => 'published',
        ]);

        return redirect()->route('content.edit', $post)->with('success', 'Post marked as published');
    }

    public function addComment(Post $post, Request $request): RedirectResponse
    {
        $this->assertPostInCompany($post);
        $user = Auth::user();

        if ($post->status !== 'published' && ! $this->userIsCreatorAdminOrOwner($post, $user)) {
            abort(403);
        }

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => $validated['comment'],
            'status_change' => null,
        ]);

        return redirect()->route('content.edit', $post)->with('success', 'Comment added');
    }

    private function assertPostInCompany(Post $post): void
    {
        abort_unless($post->company_id === Auth::user()->company_id, 403);
    }

    private function userIsCreatorAdminOrOwner(Post $post, User $user): bool
    {
        if ($post->created_by !== null && (int) $post->created_by === (int) $user->id) {
            return true;
        }

        return in_array($user->role, ['admin', 'owner'], true);
    }

    private function notifyAdminsAndOwnersNewReview(Post $post): void
    {
        $recipients = User::query()
            ->where('company_id', $post->company_id)
            ->whereIn('role', ['admin', 'owner'])
            ->whereNotNull('email')
            ->get();

        $title = $post->title !== null && $post->title !== '' ? $post->title : 'Untitled';
        $subject = 'New post ready for review: '.$title;
        $url = route('content.edit', $post);
        $body = "A new post is ready for review.\n\nPost: {$title}\n\nReview it here:\n{$url}\n";

        foreach ($recipients as $recipient) {
            try {
                Mail::raw($body, function ($message) use ($recipient, $subject) {
                    $message->to($recipient->email)->subject($subject);
                });
            } catch (\Throwable) {
                // silent
            }
        }
    }

    private function notifyCreatorApproved(Post $post): void
    {
        $creator = $post->creator;
        if ($creator === null || $creator->email === null || $creator->email === '') {
            return;
        }

        $title = $post->title !== null && $post->title !== '' ? $post->title : 'Untitled';
        $subject = 'Your post has been approved';
        $url = route('content.edit', $post);
        $body = "Your post \"{$title}\" has been approved.\n\nView it here:\n{$url}\n";

        try {
            Mail::raw($body, function ($message) use ($creator, $subject) {
                $message->to($creator->email)->subject($subject);
            });
        } catch (\Throwable) {
            // silent
        }
    }

    private function notifyCreatorRejected(Post $post, string $reason): void
    {
        $creator = $post->creator;
        if ($creator === null || $creator->email === null || $creator->email === '') {
            return;
        }

        $title = $post->title !== null && $post->title !== '' ? $post->title : 'Untitled';
        $subject = 'Your post needs revision';
        $url = route('content.edit', $post);
        $body = "Your post \"{$title}\" needs revision.\n\nReason:\n{$reason}\n\nOpen the post:\n{$url}\n";

        try {
            Mail::raw($body, function ($message) use ($creator, $subject) {
                $message->to($creator->email)->subject($subject);
            });
        } catch (\Throwable) {
            // silent
        }
    }
}

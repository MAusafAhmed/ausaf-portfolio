<?php
defined('ADMIN_PANEL') || exit;

$messages = messages_all();
?>

<section class="panel">
    <div class="panel-head">
        <h2 class="panel-title">Contact form messages</h2>
        <?php if ($unread): ?>
            <form method="post" action="actions.php">
                <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                <input type="hidden" name="action" value="messages_mark_read">
                <input type="hidden" name="back" value="dashboard.php?tab=messages">
                <button type="submit" class="btn btn-small">Mark all as read</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (SMTP_ENABLED && SMTP_PASS === ''): ?>
        <div class="alert alert-warn">
            Email is not configured yet &mdash; messages are only being stored here.
            Add your <code>SMTP_PASS</code> (Gmail App Password) in <code>api/config.php</code>.
        </div>
    <?php endif; ?>

    <?php if (!$messages): ?>
        <p class="empty">No messages have come in yet.</p>
    <?php else: ?>
        <div class="msg-list">
            <?php foreach ($messages as $m): ?>
                <article class="msg <?php echo empty($m['is_read']) ? 'unread' : ''; ?>">
                    <div class="msg-head">
                        <div>
                            <strong><?php echo e($m['name']); ?></strong>
                            <a href="mailto:<?php echo e($m['email']); ?>"><?php echo e($m['email']); ?></a>
                        </div>
                        <div class="msg-meta">
                            <?php if (empty($m['mailed'])): ?><span class="tag tag-warn">email not sent</span><?php endif; ?>
                            <time><?php echo e($m['created_at']); ?></time>
                        </div>
                    </div>
                    <h4 class="msg-subject"><?php echo e($m['subject']); ?></h4>
                    <p class="msg-body"><?php echo nl2br(e($m['message'])); ?></p>
                    <div class="msg-actions">
                        <a class="btn btn-small"
                            href="mailto:<?php echo e($m['email']); ?>?subject=<?php echo rawurlencode('Re: ' . $m['subject']); ?>">Reply</a>
                        <form method="post" action="actions.php" onsubmit="return confirm('Delete this message?');">
                            <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                            <input type="hidden" name="action" value="message_delete">
                            <input type="hidden" name="id" value="<?php echo (int) $m['id']; ?>">
                            <input type="hidden" name="back" value="dashboard.php?tab=messages">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

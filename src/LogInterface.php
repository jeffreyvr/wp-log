<?php

namespace Jeffreyvr\WPLog;

class LogInterface
{
    public string $capability = 'manage_options';

    public function __construct(public Log $log)
    {
        $this->maybeClearLog();
    }

    public function setCapability(string $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    public function maybeClearLog(): void
    {
        add_action('admin_post_clear_log_'.$this->generateSlug(), function () {
            if (! current_user_can($this->capability)) {
                wp_die('Access denied');
            }

            $this->log->clear();

            wp_redirect(wp_get_referer());

            exit;
        });
    }

    public function generateSlug(): string
    {
        return 'wp-log-'.sanitize_title($this->log->name);
    }

    public function inAdminMenu($slug = null, $icon = 'dashicons-media-text'): void
    {
        add_action('admin_menu', function () use ($slug, $icon) {
            add_menu_page(
                $this->log->name,
                $this->log->name,
                $this->capability,
                $slug ?? $this->generateSlug($this->log->name),
                [$this, 'pageRender'],
                $icon
            );
        });
    }

    public function asHiddenAdminPage($slug = null): void
    {
        add_action('admin_menu', function () use ($slug) {
            add_submenu_page(
                null,
                $this->log->name,
                $this->log->name,
                $this->capability,
                $slug ?? $this->generateSlug($this->log->name),
                [$this, 'pageRender']
            );
        });
    }

    public function asPluginLink($baseName, $slug = null, $text = 'View Log')
    {
        $this->asHiddenAdminPage($slug);

        add_filter('plugin_action_links_'.$baseName, function ($links) use ($text, $slug) {
            $links[] = '<a href="'.admin_url('admin.php?page='.($slug ?? $this->generateSlug($this->log->name))).'">'.$text.'</a>';

            return $links;
        });
    }

    public function render($limit = 25)
    {
        $items = $this->log->getItems($limit);

        $output = '';

        foreach ($items as $item) {
            $output .= '<pre class="wp-log-item">'.$item.'</pre>';
        }

        return $output;
    }

    public function pageRender(): void
    {
        $limit = ! empty($_GET['limit']) ? (int) $_GET['limit'] : 25;

        ?>
        <div class="wrap wp-log-interface">
            <h1>Log: <?php echo $this->log->name; ?></h1>

            <?php if (! $this->log->isFileWritable()) { ?>
                <div class="notice notice-error">
                    <p>Log file is not writable.</p>
                </div>
            <?php } ?>

            <div>
                <a href="<?php echo admin_url('admin.php?page=include-text&limit=25'); ?>">25</a> |
                <a href="<?php echo admin_url('admin.php?page=include-text&limit=50'); ?>">50</a> |
                <a href="<?php echo admin_url('admin.php?page=include-text&limit=100'); ?>">100</a> |
                <a href="<?php echo admin_url('admin.php?page=include-text&limit=none'); ?>">Show all</a> |
                <a href="<?php echo admin_url('admin-post.php?action=clear_log_'.$this->generateSlug()); ?>">Clear log</a>
            </div>

            <div class="log-items">
                <?php echo $this->render($limit); ?>
            </div>
        </div>
        <?php
    }
}

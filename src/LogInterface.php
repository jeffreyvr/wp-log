<?php

namespace Jeffreyvr\WPLog;

class LogInterface
{
    public string $capability = 'manage_options';
    public string|null $slug = null;

    public function __construct(public Log $log)
    {
        $this->slug = $this->generateSlug();

        $this->maybeClearLog();
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setCapability(string $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    public function maybeClearLog(): void
    {
        add_action('admin_post_clear_log_'.$this->getSlug(), function () {
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

    public function inAdminMenu($slug = null, $parent = null, $icon = 'dashicons-media-text'): void
    {
        if ($slug) {
            $this->setSlug($slug);
        }

        add_action('admin_menu', function () use ($slug, $icon, $parent) {
            if ($parent) {
                add_submenu_page(
                    $parent,
                    $this->log->name,
                    $this->log->name,
                    $this->capability,
                    $this->getSlug(),
                    [$this, 'pageRender']
                );

                return;
            }

            add_menu_page(
                $this->log->name,
                $this->log->name,
                $this->capability,
                $this->getSlug(),
                [$this, 'pageRender'],
                $icon
            );
        });
    }

    public function asHiddenAdminPage(): void
    {
        add_action('admin_menu', function () {
            add_submenu_page(
                null,
                $this->log->name,
                $this->log->name,
                $this->capability,
                $this->getSlug(),
                [$this, 'pageRender']
            );
        });
    }

    public function asPluginLink($baseName, $slug = null, $text = 'View Log')
    {
        $this->asHiddenAdminPage();

        add_filter('plugin_action_links_'.$baseName, function ($links) use ($text) {
            $links[] = '<a href="'.admin_url('admin.php?page='.$this->getSlug()).'">'.$text.'</a>';

            return $links;
        });
    }

    public function render($limit = 25)
    {
        $items = array_map(function ($item) {
            return preg_replace('/(\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/', '<strong>$1</strong>', $item);
        }, $this->log->getItems($limit));

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
                <a href="<?php echo admin_url('admin.php?page='.$this->getSlug().'&limit=25'); ?>">25</a> |
                <a href="<?php echo admin_url('admin.php?page='.$this->getSlug().'&limit=50'); ?>">50</a> |
                <a href="<?php echo admin_url('admin.php?page='.$this->getSlug().'&limit=100'); ?>">100</a> |
                <a href="<?php echo admin_url('admin.php?page='.$this->getSlug().'&limit=none'); ?>">Show all</a> |
                <a href="<?php echo admin_url('admin-post.php?action=clear_log_'.$this->getSlug()); ?>">Clear log</a>
            </div>

            <div class="log-items">
                <?php echo $this->render($limit); ?>
            </div>
        </div>
        <?php
    }
}

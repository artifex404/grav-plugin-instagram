<?php

namespace Grav\Plugin;

use Grav\Common\Cache;
use Grav\Common\Plugin;
use Grav\Common\Data\Data;
use Grav\Common\Page\Page;
use Grav\Common\GPM\Response;

class InstagramPlugin extends Plugin
{
    private const template_html = 'partials/instagram.html.twig';
    private $feeds;
    const HOUR_IN_SECONDS = 3600;

    /**
     * Return a list of subscribed events.
     *
     * @return array    The list of events of the plugin of the form
     *                      'name' => ['method_name', priority].
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    /**
     * Initialize configuration.
     */
    public function onPluginsInitialized()
    {
        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigInitialized' => ['onTwigInitialized', 0]
        ]);
    }

    /**
     * Add Twig Extensions.
     */
    public function onTwigInitialized()
    {
        $this->grav['twig']->twig->addFunction(new \Twig_SimpleFunction('instagram_feed', [$this, 'getFeed']));
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * @return string the rendered instagram feed
     */
    public function getFeed($params = [])
    {
        /** @var Page $page */
        $page = $this->grav['page'];
        /** @var Data $config */
        $config = $this->mergeConfig($page, TRUE);

        /* Get an instance of the Grav cache */
        /** @var Cache $cache */
        $cache = $this->grav['cache'];

        // Generate API url
        $url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token=' . $config->get('feed_parameters.access_token') . '&count=' . $config->get('feed_parameters.count');
        $cacheKey = 'instagram-' . md5($url);

        $template_vars = $cache->fetch($cacheKey);

        // Get the results from the live API, cached version not found
        if (!$template_vars) {
            try {
                $results = Response::get($url);
            } catch (\RuntimeException $e) {
                $this->grav['log']->error($e->getMessage());
                return;
            }

            if ($this->parseResponse($results)) { // Successfully parsed the feed
                $template_vars = [
                    'user_id' => $config->get('feed_parameters.user_id'),
                    'client_id' => $config->get('feed_parameters.client_id'),
                    'feed' => $this->feeds,
                    'count' => $config->get('feed_parameters.count'),
                    'params' => $params
                ];
                $cache->save($cacheKey, $template_vars,
                    InstagramPlugin::HOUR_IN_SECONDS * $config->get('feed_parameters.cache_time'));
            } else { // Didn't return a feed or couldn't parse what was returned
                return;
            }

        }

        $output = $this->grav['twig']->twig()->render(InstagramPlugin::template_html, $template_vars);
        return $output;

    }

    private function addFeed($result)
    {
        foreach ($result as $key => $val) {
            if (!isset($this->feeds[$key])) {
                $this->feeds[$key] = $val;
            }
        }
        krsort($this->feeds);
    }

    private function parseResponse($json)
    {
        $r = array();
        $content = json_decode($json, true);
        if ((is_array($content['data']) || $content['data'] instanceof \Countable) && count($content['data'])) {
            foreach ($content['data'] as $key => $val) {
                $created_at = $val['created_time'];
                $r[$created_at]['time'] = $created_at;
                $r[$created_at]['text'] = $val['caption']['text'];
                $r[$created_at]['image'] = $val['images']['standard_resolution']['url'];
                $r[$created_at]['image_width'] = $val['images']['standard_resolution']['width'];
                $r[$created_at]['thumb'] = $val['images']['low_resolution']['url'];
                $r[$created_at]['thumb_width'] = $val['images']['low_resolution']['width'];
                $r[$created_at]['micro'] = $val['images']['thumbnail']['url'];
                $r[$created_at]['micro_width'] = $val['images']['thumbnail']['width'];
                $r[$created_at]['user'] = $val['user']['full_name'];
                $r[$created_at]['link'] = $val['link'];
                $r[$created_at]['comments'] = $val['comments']['count'];
                $r[$created_at]['likes'] = $val['likes']['count'];
                $r[$created_at]['type'] = $val['type'];
            }
            $this->addFeed($r);
            return true;
        }

        return false; //no feed found in the json
    }
}

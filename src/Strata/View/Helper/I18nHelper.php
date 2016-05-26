<?php
namespace IP\Code\Strata\View\Helper;

use Polyglot\Plugin\Polyglot;
use Polyglot\I18n\Permalink\PostPermalinkManager;
use Polyglot\I18n\Permalink\TermPermalinkManager;
use IP\Code\Common\SlugTrait;
use Strata\Strata;

class I18nHelper extends \Strata\View\Helper\Helper {

    use SlugTrait;

    protected $i18n;

    function __construct()
    {
        $this->i18n = Strata::i18n();
    }

    public function getLocales()
    {
        return $this->i18n->getLocales();
    }

    public function getCurrentLocale()
    {
        return $this->i18n->getCurrentLocale();
    }

    public function getDefaultLocale()
    {
        return $this->i18n->getDefaultLocale();
    }

    public function isCurrentLocale($locale)
    {
        return $this->getCurrentLocale()->getCode() === $locale->getCode();
    }

    public function getCurrentUrlIn($locale)
    {
        $currentPost = get_post();
        if ($currentPost) {
            return $this->getPostUrlIn($currentPost, $locale, get_permalink($currentPost));
        }

        global $wp_query;
        $taxonomy = $wp_query->queried_object;
        if (is_a($taxonomy, "WP_Term")) {
            return $this->getTermUrlIn($taxonomy, $locale, get_term_link($taxonomy, $taxonomy->taxonomy));
        }

        return $locale->getHomeUrl();
    }

    public function getPostUrlIn($post, $locale, $currentUrl)
    {
        $permalinkManager = new PostPermalinkManager();
        $permalinkManager->enforceLocale($locale);
        return $permalinkManager->generatePermalink($currentUrl, $post->ID);
    }

    public function getTermUrlIn($taxonomy, $locale, $currentUrl)
    {
        $permalinkManager = new TermPermalinkManager();
        $permalinkManager->enforceLocale($locale);
        return $permalinkManager->generatePermalink($currentUrl, $taxonomy);
    }


    public function getTranslatedId($pageId)
    {
        if ($pageId) {
            $currentLocale = $this->getCurrentLocale();
            $translation = $currentLocale->getTranslatedPost($pageId);
            if ($translation) {
                return $translation->ID;
            }
        }

        return $pageId;
    }

    public function getOriginalId($pageId)
    {
        if ($pageId) {
            $defaultLocale = $this->getDefaultLocale();
            $translation = $defaultLocale->getTranslatedPost($pageId);
            if ($translation) {
                return $translation->ID;
            }
        }

        return $pageId;
    }

    public function isFrontPage()
    {
        $currentId = (int)get_the_ID();
        $locale = $this->getCurrentLocale();
        $homepageId = $this->i18n->query()->getDefaultHomepageId();

        if ($locale->isDefault()) {
            return $currentId === $homepageId;
        }

        return $this->getOriginalId($currentId) === $homepageId;
    }
}

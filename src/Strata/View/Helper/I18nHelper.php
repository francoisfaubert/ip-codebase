<?php
namespace IP\Code\Strata\View\Helper;

use Polyglot\Plugin\Polyglot;
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
        $currentLocale = $this->getCurrentLocale();
        $translatedPost = $locale->getTranslatedPost();

        if ($translatedPost) {
            if ((bool)Strata::config("i18n.default_locale_fallback")) {
                $replace = $currentLocale->getHomeUrl();
                $replacement = $locale->getHomeUrl();
                return str_replace($replace, $replacement, get_permalink($translatedPost->ID));
            }

            return get_permalink($translatedPost->ID);
        }

        if ((bool)Strata::config("i18n.default_locale_fallback")) {
            $originalPost = $this->getDefaultLocale()->getTranslatedPost(get_the_ID());
            if ($originalPost) {
                $originalUrl = get_permalink($originalPost);
                $replace = $currentLocale->getHomeUrl();
                $replacement = $locale->getHomeUrl();
                return str_replace($replace, $replacement, $originalUrl);
            }
        }

        return $locale->getHomeUrl();
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

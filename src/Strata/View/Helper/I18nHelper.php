<?php
namespace IP\Code\Strata\View\Helper;

use Polyglot\Plugin\Polyglot;
use IP\Code\Common\SlugTrait;
use Strata\Strata;

class I18nHelper extends \Strata\View\Helper\Helper {

    use SlugTrait;

    protected $polyglot;

    function __construct()
    {
        $this->polyglot = Polyglot::instance();
    }

    public function getLocales()
    {
        return $this->polyglot->getLocales();
    }

    public function getCurrentLocale()
    {
        return $this->polyglot->getCurrentLocale();
    }

    public function getDefaultLocale()
    {
        return $this->polyglot->getDefaultLocale();
    }

    public function isCurrentLocale($locale)
    {
        return $this->getCurrentLocale()->getCode() === $locale->getCode();
    }

    public function getCurrentUrlIn($locale)
    {
        $translatedPost = $locale->getTranslatedPost();
        if ($translatedPost) {
            return get_permalink($translatedPost->ID);
        }

        if ((bool)Strata::app()->getConfig("i18n.default_locale_fallback")) {
            $originalPost = $this->getDefaultLocale()->getTranslatedPost(get_the_ID());
            return get_permalink($originalPost->ID);
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

    public function isFrontPage()
    {
        $locale = $this->getCurrentLocale();
        $homepageId = $this->polyglot->query()->getDefaultHomepageId();
        return $locale->isTranslationOfPost($homepageId);
    }
}

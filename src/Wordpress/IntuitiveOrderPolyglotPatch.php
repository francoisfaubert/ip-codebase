<?php

if (!class_exists("Strata\Strata")) {
    throw new Exception("Polyglot Localization plugin is expected to be ran within a Strata environment.");
}

if (class_exists("Polyglot\Plugin\Adaptor\PluginAdaptor")) {
    add_action( 'wp_ajax_update-menu-order', 'hipco_patch_update_menu_order' );
    add_action( 'wp_ajax_update-menu-order-tags', 'hipco_patch_update_menu_order_tags');
}

function hipco_patch_update_menu_order()
{
    $manager = new Polyglot\I18n\Translation\PostMetaManager();

    global $wpdb;
    parse_str($_POST['order'], $data);

    if (!is_array($data)) {
        return false;
    }

    foreach ($data as $key => $values ) {
        foreach ($values as $position => $id) {
            $manager->filter_onSavePost((int)$id);
        }
    }
}

function hipco_patch_update_menu_order_tags()
{
    $i18n = Strata\Strata::i18n();
    $currentLocale = $i18n->getCurrentLocale();
    $locales = $i18n->getLocales();

    if ($currentLocale->isDefault()) {
        global $wpdb;
        parse_str($_POST['order'], $data);

        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $key => $values ) {
            foreach ($values as $position => $id ) {

                $order = $wpdb->get_var("SELECT term_order FROM $wpdb->terms WHERE term_id = " . intval($id));

                foreach ($locales as $locale) {
                    if (!$locale->isDefault() && $locale->hasTermTranslation($id)) {
                        $translation = $locale->getTermTranslation($id);
                        if ($translation) {
                            $wpdb->update(
                                $wpdb->terms,
                                array('term_order' => $order),
                                array('term_id' => intval($translation->term_id))
                            );
                        }
                    }
                }
            }
        }
    }
}



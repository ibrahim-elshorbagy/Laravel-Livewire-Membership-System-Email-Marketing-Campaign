<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_HTMLDefinition;

class HtmlPurifierService
{
    public function purifyFullHtml(string $dirtyHtml): string
    {
        $config = HTMLPurifier_Config::createDefault();

        // CSS configurations
        $config->set('CSS.AllowTricky', true);
        $config->set('CSS.AllowImportant', true);
        $config->set('CSS.Trusted', true);
        $config->set('CSS.AllowedProperties', null);
        $config->set('CSS.MaxImgLength', null);
        $config->set('CSS.Proprietary', true);
        $config->set('CSS.AllowedFonts', null);

        // HTML configurations
        $config->set('HTML.Trusted', true);
        $config->set('HTML.AllowedElements', null);
        $config->set('HTML.AllowedAttributes', null);

        $def = $config->getHTMLDefinition(true);
        $this->addHtmlElements($def);

        $purifier = new HTMLPurifier($config);
        return $purifier->purify($dirtyHtml);
    }

    private function addHtmlElements(HTMLPurifier_HTMLDefinition $def): void
    {
        // Basic HTML structure
        $def->addElement('html', 'Document', 'required: head | body', null, [
            'dir' => 'Enum#ltr,rtl',
            'lang' => 'Text',
            'style' => 'Text',
            'class' => 'Text'
        ]);

        // Head element
        $def->addElement('head', false, 'optional: meta | link | style | title', null, [
            'profile' => 'Text'
        ]);

        // Body element
        $def->addElement('body', false, 'Flow', 'Common', [
            'style' => 'Text',
            'class' => 'Text',
            'dir' => 'Enum#ltr,rtl',
            'background' => 'URI',
            'bgcolor' => 'Color'
        ]);

        // Style element
        $def->addElement('style', 'Block', 'required: #PCDATA', null, [
            'type' => 'Text',
            'media' => 'Text'
        ]);
    }
}

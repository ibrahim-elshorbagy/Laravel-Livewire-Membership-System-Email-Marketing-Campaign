<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_HTMLDefinition;

class HtmlPurifierService
{
    public function purifyFullHtml(string $dirtyHtml): string
    {
        // Preserve DOCTYPE if it exists
        $doctype = '';
        $matches = [];
        if (preg_match('/^(<!DOCTYPE[^>]+>)/i', $dirtyHtml, $matches)) {
            $doctype = $matches[1];
        }

        $config = HTMLPurifier_Config::createDefault();

        // Core configuration
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.SerializerPath', storage_path('app/purifier'));
        $config->set('Cache.SerializerPermissions', 0755);
        $config->set('URI.DisableExternal', true);
        $config->set('URI.DisableExternalResources', true);

        // Preserve the full document structure
        $config->set('Core.ConvertDocumentToFragment', false);
        $config->set('HTML.Parent', '__document_root');
        $config->set('Core.EscapeInvalidTags', false);  // Allow more tags
        $config->set('Core.LexerImpl', 'DirectLex');

        // Security and feature configuration
        $config->set('CSS.AllowImportant', false);
        $config->set('CSS.Trusted', false);
        $config->set('HTML.Trusted', false);
        $config->set('URI.AllowedSchemes', ['data' => true, 'http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);

        // Get HTML definition
        $def = $config->getHTMLDefinition(true);
        $this->addFullDocumentSupport($def);

        $purifier = new HTMLPurifier($config);
        $cleanHtml = $purifier->purify($dirtyHtml);

        // Reattach the DOCTYPE if it was present
        return $doctype ? $doctype . "\n" . $cleanHtml : $cleanHtml;
    }

    private function addFullDocumentSupport(HTMLPurifier_HTMLDefinition $def): void
    {
        // Add document root element
        $def->addElement('__document_root', false, 'required: html', null);

        // Define HTML element with all possible attributes
        $def->addElement('html', 'Document', 'required: head | body', null, [
            'dir' => 'Enum#ltr,rtl',
            'lang' => 'Text',
            'xml:lang' => 'Text',
            'xmlns' => 'Text',
            'xmlns:o' => 'Text',
            'xmlns:v' => 'Text',
            'xmlns:w' => 'Text',
        ]);


        // Define head element with all possible elements - ADD TITLE SUPPORT HERE
        $def->addElement('head', false, 'optional: title | meta | link | style | script | noscript | xml', null, [
            'profile' => 'Text',
        ]);

        // Add title element explicitly
        $def->addElement('title', false, 'required: #PCDATA', null, [
            'dir' => 'Enum#ltr,rtl',
            'lang' => 'Text',
        ]);

        // Define body element
        $def->addElement('body', false, 'Flow', 'Common', [
            'style' => 'Text',
            'class' => 'Text',
            'dir' => 'Enum#ltr,rtl',
            'lang' => 'Text',
            'xml:lang' => 'Text',
            'background' => 'URI',
            'bgcolor' => 'Color',
        ]);


        // Add meta, style, and other elements with their attributes
        $def->addElement('meta', false, 'Empty', null, [
            'name' => 'NMTOKENS',
            'content' => 'Text',
            'charset' => 'Text',
            'http-equiv' => 'Text',
            'itemprop' => 'Text',
            'property' => 'Text',
        ]);

        $def->addElement('style', 'Inline', 'required: #PCDATA', null, [
            'type' => 'Text',
            'media' => 'Text',
        ]);

    }
}

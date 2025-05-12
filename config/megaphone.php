<?php

return [
    /*
     * Model that has the "Notifiable" and "HasMegaphone" Traits
     */
    'model' => \App\Models\User::class,

    /*
     * Array of all the notification types to display in Megaphone
     */
    'types' => [
        \MBarlow\Megaphone\Types\General::class,
        \MBarlow\Megaphone\Types\NewFeature::class,
        \MBarlow\Megaphone\Types\Important::class,
    ],

    /*
     * Custom notification types specific to your App
     */
    'customTypes' => [
        /*
            Associative array in the format of
            \Namespace\To\Notification::class => 'path.to.view',
         */
    ],

    /*
     * Array of Notification types available within MegaphoneAdmin Component or
     * leave as null to show all types / customTypes
     *
     * 'adminTypeList' => [
     *     \MBarlow\Megaphone\Types\NewFeature::class,
     *     \MBarlow\Megaphone\Types\Important::class,
     * ],
     */
    'adminTypeList' => null,

    /*
     * Clear Megaphone notifications older than....
     * @deprecated
     * @see "megaphone.clearNotifications.autoClearAfter"
     */
    'clearAfter' => '2 weeks',

    /*
     * Option for setting the icon to show actual count of unread Notifications or
     * show a dot instead
     */
    'showCount' => true,

    /*
     * Enable Livewire Poll feature for auto updating.
     * See livewire docs for poll option descriptions
     * @link https://livewire.laravel.com/docs/wire-poll
     */
    'poll' => [
        'enabled' => false,

        'options' => [
            'time' => '15s',
            'keepAlive' => false,
            'viewportVisible' => false,
        ],
    ],

    /*
     * Options relating to the clearing out of notifications.
     * Enable the ability for users to delete notifications themselves.
     * Set the timeframe, after which read notifications will be auto cleared.
     */
    'clearNotifications' => [
        'userCanDelete' => true,

        'autoClearAfter' => '2 weeks',
    ],
];

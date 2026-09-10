<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Media Disks
    |--------------------------------------------------------------------------
    |
    | Logos and films are stored separately because they behave differently.
    | Logos are small, static and committed to the repository, so they ship
    | with a deploy and stay on the public disk. Films are large and uploaded
    | through the studio, so they belong in object storage: set FILM_DISK=s3
    | with the Cloudflare R2 credentials.
    |
    */

    'logos' => env('LOGO_DISK', 'public'),

    'films' => env('FILM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),

            // Cloudflare R2 notes:
            // - R2 has no ACLs, so visibility is deliberately left unset.
            //   Public reads come from the bucket's custom domain binding.
            // - The AWS SDK adds CRC32 checksums to every request by default
            //   since 3.337. R2 rejects those, so they are only sent when the
            //   API actually requires them.
            'request_checksum_calculation' => env('AWS_REQUEST_CHECKSUM_CALCULATION', 'when_required'),
            'response_checksum_validation' => env('AWS_RESPONSE_CHECKSUM_VALIDATION', 'when_required'),

            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

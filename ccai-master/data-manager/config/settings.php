<?php

return [
    'menus' => [
        'dashboard' => [
            [
                'title' => 'Data Inventory',
                'icon' => 'list-ul',
                'url' => 'inventory',
                'color' => 'blue',
                'initials' => 'DI',
            ],
            [
                'title' => 'Horizon Queue Monitor',
                'icon' => 'database',
                'url' => 'horizon',
                'color' => 'blue',
                'initials' => 'QU',
            ],
            [
                'title' => 'Sync source material',
                'icon' => 'sync',
                'url' => 'dataset/sync',
                'color' => 'blue',
                'initials' => 'SY',
            ]
        ]
    ],

    # Faster Whisper transcriptions
    'transcription' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/transcribe.py',
    ],

    # Gemini transcriptions
    'transcription_gemini' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/gemini.py',
    ],

    # Gemini translations
    'translation_gemini' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/translation_gemini.py',
    ],

    # Gemini diarization
    'diarization_gemini' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/diarization.py',
    ],

    # Write CCAI conversation to GCS
    'gcs_upload' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/dump_conversation_to_bucket.py',
    ],

    # BigQuery script
    'bigquery' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/bigquery_ingest.py',
        'table' => 'ccai-dev-project.gemini_coversation_analysis.gemini_insights'
    ],

    'slicer' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/data-manager/slicer.py',
        # Where the sliced pieces will be stored
        'destination_folder' => '/var/www/ccai/data-manager/storage/app/swanglish'
    ],

    # Sentiment Analysis
    'sentiment_analysis' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/sentiment_analysis.py',
    ],

    # Transcript summarization
    'summarization' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/summarization.py',
    ],

    # Sentiment Analysis
    'topic_extraction' => [
        'python_env' => '/opt/conda/bin/python',
        'script' => '/var/www/ccai/scripts/topic_extraction.py',
    ],

    'dlp' => [
        'project_id' => 'ccai-dev-project',
        'info_types' => [
            'ETHNIC_GROUP',
            'AGE',
            'FINANCIAL_ACCOUNT_NUMBER',
            'CREDIT_CARD_NUMBER',
            'PHONE_NUMBER',
            'EMAIL_ADDRESS',
        ]
    ],

    'status' => [
        'ACTIVE' => 'Active',
        'DISABLED' => 'Disabled',
    ],

    'buckets' => [
        'source_materials' => 'source_audio_files',
    ]
];

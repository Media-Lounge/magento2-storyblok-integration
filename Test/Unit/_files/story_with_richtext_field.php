<?php
return [
    'story' => [
        'name' => 'Test Story',
        'parent_id' => 0,
        'group_id' => '123456-123456-123456-123456',
        'alternates' => [],
        'created_at' => '2020-12-28T15:01:27.432Z',
        'sort_by_date' => null,
        'tag_list' => [],
        'updated_at' => '2020-12-28T15:01:27.432Z',
        'published_at' => null,
        'id' => 12345678,
        'uuid' => '123456-123456-123456-123456',
        'is_folder' => false,
        'content' => [
            '_uid' => '12345-12345-12345-12345',
            'component' => 'page',
            'body' => [
                0 => [
                    '_uid' => '12345-123-3123-2123-3312312312',
                    'content' => [
                        'type' => 'doc',
                        'content' => [
                            0 => [
                                'type' => 'heading',
                                'attrs' => [
                                    'level' => 1,
                                    'data-test' => null,
                                ],
                                'content' => [
                                    0 => [
                                        'type' => 'text',
                                        'text' => 'Test Header',
                                    ],
                                ],
                            ],
                            1 => [
                                'type' => 'paragraph',
                                'content' => [
                                    0 => [
                                        'type' => 'text',
                                        'text' => 'Test paragraph ',
                                    ],
                                    1 => [
                                        'type' => 'text',
                                        'marks' => [
                                            0 => [
                                                'type' => 'bold',
                                            ],
                                        ],
                                        'text' => 'bold',
                                    ],
                                    2 => [
                                        'type' => 'text',
                                        'text' => ' ',
                                    ],
                                    3 => [
                                        'type' => 'text',
                                        'marks' => [
                                            0 => [
                                                'type' => 'italic',
                                            ],
                                        ],
                                        'text' => 'italic',
                                    ],
                                    4 => [
                                        'type' => 'text',
                                        'text' => ' ',
                                    ],
                                    5 => [
                                        'type' => 'text',
                                        'marks' => [
                                            0 => [
                                                'type' => 'link',
                                                'attrs' => [
                                                    'href' => '#',
                                                    'uuid' => null,
                                                    'linktype' => 'url',
                                                    'target' => '_blank',
                                                    'anchor' => null,
                                                ],
                                            ],
                                        ],
                                        'text' => 'link',
                                    ],
                                    6 => [
                                        'type' => 'text',
                                        'text' => ' ',
                                    ],
                                    7 => [
                                        'type' => 'text',
                                        'marks' => [
                                            0 => [
                                                'type' => 'strike',
                                            ],
                                        ],
                                        'text' => 'strikethrough',
                                    ],
                                    8 => [
                                        'type' => 'text',
                                        'text' => ' ',
                                    ],
                                    9 => [
                                        'type' => 'text',
                                        'marks' => [
                                            0 => [
                                                'type' => 'underline',
                                            ],
                                        ],
                                        'text' => 'underline',
                                    ],
                                    10 => [
                                        'type' => 'text',
                                        'text' => ' ',
                                    ],
                                    11 => [
                                        'type' => 'unknown',
                                        'content' => [
                                            0 => [
                                                'type' => 'text',
                                                'text' => 'unknown',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            2 => [
                                'type' => 'bullet_list',
                                'content' => [
                                    0 => [
                                        'type' => 'list_item',
                                        'content' => [
                                            0 => [
                                                'type' => 'paragraph',
                                                'content' => [
                                                    0 => [
                                                        'type' => 'text',
                                                        'text' => 'List Item 1',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    1 => [
                                        'type' => 'list_item',
                                        'content' => [
                                            0 => [
                                                'type' => 'paragraph',
                                                'content' => [
                                                    0 => [
                                                        'type' => 'text',
                                                        'text' => 'List Item 2',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            3 => [
                                'type' => 'paragraph',
                                'content' => [
                                    0 => [
                                        'type' => 'image',
                                        'attrs' => [
                                            'src' =>
                                                'https://a.storyblok.com/f/123123/800x600/123123123/image.jpg',
                                        ],
                                    ],
                                ],
                            ],
                            4 => [
                                'type' => 'paragraph',
                                'content' => [
                                    0 => [
                                        'type' => 'image',
                                        'attrs' => [
                                            'src' =>
                                                'https://a.storyblok.com/f/123123/800x600/123123123/image.jpg',
                                            'alt' => '',
                                            'title' => '',
                                        ],
                                    ],
                                ],
                            ],
                            5 => [
                                'type' => 'ordered_list',
                                'attrs' => [
                                    'order' => 1,
                                ],
                                'content' => [
                                    0 => [
                                        'type' => 'list_item',
                                        'content' => [
                                            0 => [
                                                'type' => 'paragraph',
                                                'content' => [
                                                    0 => [
                                                        'type' => 'text',
                                                        'text' => 'List Item 1',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    1 => [
                                        'type' => 'list_item',
                                        'content' => [
                                            0 => [
                                                'type' => 'paragraph',
                                                'content' => [
                                                    0 => [
                                                        'type' => 'text',
                                                        'text' => 'List Item 2',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            6 => [
                                'type' => 'blockquote',
                                'content' => [
                                    0 => [
                                        'type' => 'paragraph',
                                        'content' => [
                                            0 => [
                                                'type' => 'text',
                                                'text' => 'Test Quote',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            7 => [
                                'type' => 'horizontal_rule',
                            ],
                            8 => [
                                'type' => 'code_block',
                                'attrs' => [
                                    'class' => 'language-php',
                                ],
                                'content' => [
                                    0 => [
                                        'type' => 'text',
                                        'text' => 'echo "test";',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'component' => 'description',
                    '_editable' => '',
                ],
            ],
            '_editable' => '',
        ],
        'published' => false,
        'slug' => 'test-story',
        'path' => null,
        'full_slug' => 'test-story',
        'default_root' => '',
        'disble_fe_editor' => false,
        'parent' => null,
        'is_startpage' => false,
        'unpublished_changes' => false,
        'meta_data' => null,
        'imported_at' => null,
        'preview_token' => ['token' => 'test-token', 'timestamp' => '1609167687'],
        'pinned' => false,
        'breadcrumbs' => [],
        'publish_at' => null,
        'expire_at' => null,
        'first_published_at' => null,
        'last_author' => [
            'id' => 51004,
            'userid' => 'user@email.com',
            'friendly_name' => 'user@email.com',
        ],
        'user_ids' => [],
        'space_role_ids' => [],
        'translated_slugs' => [],
        'localized_paths' => [],
        'position' => -10,
        'translated_stories' => [],
        'can_not_view' => false,
        'lang' => '',
    ],
    '_storyblok' => 12345678,
];

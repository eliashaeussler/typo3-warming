..  include:: /Includes.rst.txt

..  _logging:

=======
Logging
=======

..  versionadded:: 1.1.0

    `Feature: #374 - Introduce logging for default crawlers <https://github.com/eliashaeussler/typo3-warming/pull/374>`__

When using the default crawlers provided by this extension, crawling
results are logged using a configured log writer. By default, TYPO3
configures a file writer with minimum log level `warning`.

However, you can always override logging configuration within your
:file:`config/system/settings.php` or :file:`config/system/additional.php`
file:

..  code-block:: php
    :caption: config/system/settings.php

    return [
        'LOG' => [
            'EliasHaeussler' => [
                'Typo3Warming' => [
                    'Crawler' => [
                        // Default crawler
                        'ConcurrentUserAgentCrawler' => [
                            'writerConfiguration' => [
                                \Psr\Log\LogLevel::WARNING => [
                                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                                        // A. Disable logging
                                        'disabled' => true,

                                        // -or- B. Configure logger
                                        'logFileInfix' => 'warming',
                                    ],
                                ],
                            ],
                        ],

                        // Verbose crawler
                        'OutputtingUserAgentCrawler' => [
                            'writerConfiguration' => [
                                \Psr\Log\LogLevel::WARNING => [
                                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                                        // A. Disable logging
                                        'disabled' => true,

                                        // -or- B. Configure logger
                                        'logFileInfix' => 'warming',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

..  seealso::

    Read more about logging in the :ref:`official TYPO3 documentation <t3coreapi:logging>`.

..  _custom-log-table:

Custom log table
================

The extension ships with a custom log table `tx_warming_domain_model_log`.
In addition, a custom log writer
:php:`\EliasHaeussler\Typo3Warming\Log\Writer\DatabaseWriter`
is provided that writes all logged crawling results to said table.

..  note::

    All log entries are written to the root page (uid=0).

Note that this log writer is not enabled by default. You must explicitly
enable it in your :file:`config/system/settings.php` or
:file:`config/system/additional.php` file:

..  code-block:: php
    :caption: config/system/settings.php

    return [
        'LOG' => [
            'EliasHaeussler' => [
                'Typo3Warming' => [
                    'Crawler' => [
                        // Default crawler
                        'ConcurrentUserAgentCrawler' => [
                            'writerConfiguration' => [
                                \Psr\Log\LogLevel::WARNING => [
                                    \EliasHaeussler\Typo3Warming\Log\Writer\DatabaseWriter::class => [],
                                ],
                            ],
                        ],

                        // Verbose crawler
                        'OutputtingUserAgentCrawler' => [
                            'writerConfiguration' => [
                                \Psr\Log\LogLevel::WARNING => [
                                    \EliasHaeussler\Typo3Warming\Log\Writer\DatabaseWriter::class => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

..  seealso::

    View the sources on GitHub:

    -   `DatabaseWriter <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Log/Writer/DatabaseWriter.php>`__

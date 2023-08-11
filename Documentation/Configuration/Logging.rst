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
:file:`system/settings.php` or :file:`system/additional.php` file:

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

    Read more about logging in the
    :ref:`official TYPO3 documentation <t3coreapi:logging>`.

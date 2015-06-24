<?php

$header = <<<EOF
This file is part of the MassiveSearchBundle

(c) MASSIVE ART WebServices GmbH

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    ->fixers(array(
        'header_comment',
        '-psr0',
        'psr4',
        'symfony',
        'concat_with_spaces',
        'concat_without_spaces',
        '-phpdoc_indent',
        '-phpdoc_params',
    ))
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    )
; 

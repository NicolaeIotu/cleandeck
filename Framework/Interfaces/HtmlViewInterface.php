<?php

namespace Framework\Interfaces;

interface HtmlViewInterface
{
    public function __construct(string     $main_content_file,
                                array      $data);
    public function __toString(): string;
}

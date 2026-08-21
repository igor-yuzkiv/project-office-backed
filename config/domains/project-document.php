<?php

$maxDepth = env('PROJECT_DOCUMENT_MAX_DEPTH');

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum Nesting Depth
    |--------------------------------------------------------------------------
    |
    | How deep the document tree of a project may go. Depth is zero-based, so
    | the default of 2 allows three levels, and a document at this depth
    | cannot have children. This is the only place the limit is defined.
    |
    | An empty or non-numeric environment value falls back to the default rather
    | than casting to zero, which would forbid nesting altogether.
    |
    */

    'max_depth' => is_numeric($maxDepth) ? (int) $maxDepth : 2,

];

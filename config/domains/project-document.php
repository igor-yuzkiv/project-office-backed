<?php

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
    */

    'max_depth' => (int) env('PROJECT_DOCUMENT_MAX_DEPTH', 2),

];

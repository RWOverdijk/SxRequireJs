<?php
return array (
    'view_manager' => array(
        'template_path_stack' => array(
            'sxrequirejs' 	=> __DIR__ . '/../view',
        ),
    ),
	'view_helpers' => array (
		'invokables' => array (
			'requirejs' => 'SxRequireJs\View\Helper\SxRequireJs',
		)
	)
);

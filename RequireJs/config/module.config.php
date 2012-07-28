<?php
return array (
    'view_manager' => array(
        'template_path_stack' => array(
            'manipulate'	=> __DIR__ . '/../view',
            'requirejs' 	=> __DIR__ . '/../view',
        ),
    ),
	'view_helpers' => array (
		'invokables' => array (
			'requirejs' => 'Manipulate\View\Helper\RequireJs',
		)
	)
);

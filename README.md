# ddd-paginator
Paginator based on pagerfanta

Basic criteria array structure example:

$criteria = [
  'field' => [
      'operator' => '',
      'value' => ''
];

Criteria array structure example (between):

$criteria = [
  'field' => [
      'operator' => 'between',
      'value' => [
          'value1',
          'value2',
      ],
  ],
];

Criteria array structure example (OR):

$criteria = [
            'name' => [
                'operator' => 'or',
                'value' => [
                    [
                        'field' => 'name',
                        'operator' => 'eq',
                        'value' => 'A'
                    ],
                    [
                        'field' => 'surname',
                        'operator' => 'eq',
                        'value' => 'B'
                    ]
                ]
            ]
        ];

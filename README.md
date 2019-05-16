# ddd-paginator
Paginator based on pagerfanta

Basic criteria array structure example:
````
$criteria = [
  'field' => 'field_name'
  'operator' => 'and|or|eq|...',
  'value' => 'value_1'
];
````

Criteria array structure example (between):
````
$criteria = [
  'field' => [
      'operator' => 'between',
      'value' => [
          'value_1',
          'value_2',
      ],
  ],
  ...
];
````

Criteria array structure example (OR):
````
$criteria = [
    'alias?.field_name' => [
        'operator' => 'or',
        'value' => [
            [
                'field' => 'field_name',
                'operator' => 'eq',
                'value' => 'value_1'
            ],
            [
                'field' => 'field_name',
                'operator' => 'eq',
                'value' => 'value_2'
            ],
            ...
        ],
    ],
    ...
];
````

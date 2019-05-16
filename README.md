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
  'C' => [
    'operator' => 'and',
    'value' => true
  ],
  'random_key' => [
    'operator' => 'or',
    'value' => [
      [
        'operator' => 'like',
        'field' => 'A',
        'value' => 'aaaa'
      ],
      [
        'operator' => 'like',
        'field' => 'B',
        'value' => 'bbbb'
      ]
    ]
  ]
];

// Output (DQL): 
// SELECT * FROM table_name WHERE C = true AND (A LIKE 'aaaa' OR B LIKE 'bbbb')
````

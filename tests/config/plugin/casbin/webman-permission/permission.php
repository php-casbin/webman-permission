<?php

return [
    'default' => [
        'model' => [
            'config_type' => 'text',
            'config_text' => '
[request_definition]
r = sub, obj, act

[policy_definition]
p = sub, obj, act

[role_definition]
g = _, _

[policy_effect]
e = some(where (p.eft == allow))

[matchers]
m = g(r.sub, p.sub) && r.obj == p.obj && r.act == p.act
            ',
        ],
        'adapter' => \Casbin\WebmanPermission\Adapter\DatabaseAdapter::class,
    ],
    'other' => [
        'model' => [
            'config_type' => 'text',
            'config_text' => '
[request_definition]
r = sub, obj, act

[policy_definition]
p = sub, obj, act

[role_definition]
g = _, _

[policy_effect]
e = some(where (p.eft == allow))

[matchers]
m = g(r.sub, p.sub) && r.obj == p.obj && r.act == p.act
            ',
        ],
        'adapter' => \Casbin\WebmanPermission\Adapter\DatabaseAdapter::class,
        'adapter_config' => [
            'table' => 'other_casbin_rule'
        ],
    ],
    'log' => [
        'enabled' => false,
        'logger' => 'casbin',
        'path' => runtime_path() . '/logs/casbin.log',
    ],
];
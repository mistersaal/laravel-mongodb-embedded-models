# Laravel Mongodb embedded models

A more **productive** and simpler solution for embedding models in mongodb models than custom casts in Laravel.
This package is extension for **jenssegers/mongodb** package.

In new develop version of [*jenssegers/mongodb*](https://github.com/jenssegers/laravel-mongodb/tree/develop)
for Laravel 7 removed embedding relations.
Instead of relations, it is proposed to use custom casts of Laravel.
But custom casts is really bad for performance ([read more](https://github.com/laravel/framework/issues/31778))
when we have multi-level embedding, for example:
User:
```json
{
  "name": "Stepan",
  "email": "myemail@gmail.com",
  "facebookAccount": {
    "accessToken": "",
    "id": 123
  },
  "albums": [
    {
      "name": "My photos",
      "photos": [
        {
          "path": "./me.jpg",
          "description": "Just a photo"
        },
        {
          "path": "./qwerty.jpg",
          "description": "Another photo"
        }
      ]
    }
  ]
}
```
Laravel calls cast set method on every action with model,
and all of embedded models is converting to array
every time.
I may then post more detailed test results, but believe me,
when there are a lot of calls to the model,
custom casts load the system very much.

## Installation
Make sure you have the [*jenssegers/mongodb develop*](https://github.com/jenssegers/laravel-mongodb/tree/develop) installed.

Install the package via Composer:
```
composer require mistersaal/laravel-mongodb-embedded-models
```

## Usage
Your every Model where there is an embedded model should `implements Mistersaal\Mongodb\Embed\HasEmbeddedModelsInterface`
and `use Mistersaal\Mongodb\Embed\HasEmbeddedModels` trait.

Every model must has `$fillable` or `$guarded` property for mass assignment.

In every Model where there is an embedded model you should define
`protected $embedMany = [];` or/and `protected $embedOne = [];` where key is name of attribute and value is Model class name.

In embedOne relation, the attribute will be cast to the Model when the Model is received from the database or.
In embedMany relation, the attribute will be cast to the Laravel Collection of Models.
When Model is saving to database, Models is converting to array of attributes.

In every Model where there is an embedded model, you should define constructor method:
```php
public function __construct($attributes = []) {
    parent::__construct($attributes);
    $this->setEmbeddedAttributes();
}
```


Example of code:
```php
namespace App;

use Mistersaal\Mongodb\Embed\HasEmbeddedModelsInterface;
use Mistersaal\Mongodb\Embed\HasEmbeddedModels;
use Jenssegers\Mongodb\Eloquent\Model;

class User extends Model implements HasEmbeddedModelsInterface
{
    use HasEmbeddedModels;

    protected $connection = 'mongodb';
    protected $guarded = [];

    public function __construct($attributes = []) {
        parent::__construct($attributes);
        $this->setEmbeddedAttributes();
    }

    protected $embedMany = [
        'albums' => Album::class,
    ];
    protected $embedOne = [
        'facebookAccount' => FacebookAccount::class,
    ];

}

class Album extends Model implements HasEmbeddedModelsInterface
{
    use HasEmbeddedModels;

    protected $connection = 'mongodb';
    protected $guarded = [];

    public function __construct($attributes = []) {
        parent::__construct($attributes);
        $this->setEmbeddedAttributes();
    }

    protected $embedMany = [
        'photos' => Photo::class,
    ];

}

class Photo extends Model
{

    protected $connection = 'mongodb';
    protected $guarded = [];

}

class FacebookAccount extends Model
{

    protected $connection = 'mongodb';
    protected $guarded = [];

}
```

If you want to override static **boot** method, you should save it initial structures:
```php
protected static function boot()
{
    parent::boot();
    static::retrieved(function ($model) {
        $model->setEmbeddedAttributes();
    });
    static::saving(function ($model) {
        $model->setSerializedEmbeddedAttributes();
    });
}
```
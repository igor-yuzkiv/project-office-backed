

- сутність Tag яка зберігатиме усі теги які тільки є в системі
	- name
	- color
- для звязків використати поліморфні звязки

example
```php
class TagModel extends Model  
{  
    use HasUlids, Searchable;  
  
    protected $table = 'tags';  
  
    public $timestamps = false;  
  
    public $incrementing = false;  
  
    protected $fillable = [  
        'id',  
        'name',  
        'color',  
    ];  
  
    public function tasks(): MorphToMany  
    {  
        return $this->morphedByMany(TaskModel::class, 'taggable', 'taggables', 'tag_id', 'taggable_id');  
    }  
  
    public function projects(): MorphToMany  
    {  
        return $this->morphedByMany(ProjectModel::class, 'taggable', 'taggables', 'tag_id', 'taggable_id');  
    }  
  
    public function toSearchableArray(): array  
    {  
        return [  
            'name' => $this->name,  
        ];  
    }  
}

<?php  
  
namespace App\Infrastructure\Models;  
  
use App\Domains\Common\Enums\Priority;  
use App\Domains\Project\Enums\TaskType;  
use App\Domains\Task\Enums\TaskStatus;  
use Illuminate\Database\Eloquent\Concerns\HasUlids;  
use Illuminate\Database\Eloquent\Model;  
use Illuminate\Database\Eloquent\Relations\MorphToMany;  
use Laravel\Scout\Searchable;  
  
class TaskModel extends Model  
{  
    use HasUlids, Searchable;  
  
    protected $table = 'tasks';  
  
    public $incrementing = false;  
  
    protected $attributes = [  
        'status' => TaskStatus::OPEN,  
        'type'   => TaskType::TASK,  
    ];  
  
    protected $fillable = [  
        'id',  
        'key',  
        'project_id',  
        'name',  
        'status',  
        'type',  
        'priority',  
        'description',  
    ];  
  
    protected $casts = [  
        'status'   => TaskStatus::class,  
        'type'     => TaskType::class,  
        'priority' => Priority::class,  
    ];  
  
    public function tags(): MorphToMany  
    {  
        return $this->morphToMany(TagModel::class, 'taggable', 'taggables', 'taggable_id', 'tag_id');  
    }  
  
    public function toSearchableArray(): array  
    {  
        return [  
            'name' => $this->name,  
            'key'  => $this->key,  
        ];  
    }  
}
```

- поки тільки 2 сутності будуть підтримувати теги це проекти і задача
- всі апі які повертають проект чи задача завжди мають включати теги але тільки обмежену кікльість наприклад 4
- для перегляду усіх тегів на details page сутності буде кнопка view all яка в dialog буде показувати усі теги по сутності
	- тумаю це можна зробити в вигляді таблиці з 2 колорнками або списку
	- таг - кількість раз скільки він використовуєтьсяф
	- для цьго TagsController буде мати ендпоінт який повертатим по сутності усі теги
		- ідея для назви get record tags

- на фронті 
	- окремий компоент для редагування тегів сутності
	- окреми компоент для перегляду тегів по рекорту (dialog)
	- окремий компоент для відображення як і одного так і деклькох тегів (список)
	- зміни в EditTaskPage
		- додаи компонет для редагування тегів 
		- думаю для початку можна обійтись тим що цей компоент завжди працюватиме з унікальним набором строк
		- а на бекнді вже під час зберження сутності викликатиме окремий handler (на рівні домену tag) який прийматиме набір цих строк і створюватиме чи оновлюватиме їх в бд
		- і повертатиме колекцію для сінку релейшенів
		- тільки треба щось придумати з кольорами

reference з ішого проекту
- /var/www/task-manager/_old_/pet.task-manager/app/Domains/Tag
- /var/www/task-manager/_old_/pet.task-manager/resources/js/features/tag
- /var/www/task-manager/_old_/pet.task-manager/resources/js/entities/tag
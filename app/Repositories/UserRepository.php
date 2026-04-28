<?php

namespace App\Repositories;

use App\Models\User;

/**
 * Class UserRepository
 * @package App\Repositories
 * @version June 12, 2018, 4:46 pm +07
 *
 * @method User findWithoutFail($id, $columns = ['*'])
 * @method User find($id, $columns = ['*'])
 * @method User first($columns = ['*'])
 */
class UserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'user_id'
    ];
    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }

    public function findWhereBuild(array $where)
    {
        $this->applyCriteria();
        $this->applyScope();
        $this->applyConditions($where);
        $model = $this->model;
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function findWithTrashed($id)
    {
        $model = $this->model;
        return $model->withTrashed()->find($id);
    }

    public function findBuild()
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->newQuery();
        $this->resetModel();
        return $this->parserResult($model);
    }
}

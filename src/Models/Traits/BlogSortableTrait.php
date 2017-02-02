<?php

namespace Soda\Blog\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Rutorika\Sortable\SortableTrait as BaseSortableTrait;

/**
 * Class SortableTrait.
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 *
 * @property string $sortableGroupField
 *
 * @method null creating($callback)
 * @method QueryBuilder on($connection = null)
 * @method QueryBuilder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method float|int max($column)
 */
trait BlogSortableTrait
{
    use BaseSortableTrait;

    protected static $reversed = false;

    /**
     * Adds position to model on creating event.
     */
    public static function bootSortableTrait()
    {
        static::creating(
            function ($model) {
                $model->positionToFirst($model);
            }
        );

        static::saving(
            function ($model) {
                if ($model->checkSortableGroupFieldChanged()) {
                    $model->positionToFirst($model, false);
                }
            }
        );

        static::addGlobalScope('position', function (Builder $builder) {
            $sortableFields = (array) config('soda.blog.default_sort');

            foreach ($sortableFields as $field => $direction) {
                $builder->orderBy($field, static::isReversed() ? (strtolower($direction) == 'desc' ? 'ASC' : 'DESC') : $direction);
            }
        });
    }

    public static function reverseOrder()
    {
        static::$reversed = true;

        return new static;
    }

    public static function normalOrder()
    {
        static::$reversed = false;
    }

    public static function isReversed()
    {
        return static::$reversed ? true : false;
    }

    /**
     * @param      $model
     * @param bool $nullOnly
     */
    protected function positionToFirst($model, $nullOnly = true)
    {
        /* @var Model $model */
        $sortableField = static::getSortableField();
        $query = static::applySortableGroup(static::on(), $model);
        $first = $query->first();

        // only automatically calculate next position with max+1 when a position has not been set already
        if (! $nullOnly || $model->$sortableField === null) {
            $model->setAttribute($sortableField, $query->max($sortableField) + 1);
            if ($first) {
                $model->move('moveBefore', $first, false);
            }
        }
    }

    /**
     * @param string $action moveAfter/moveBefore
     * @param Model  $entity
     * @param bool   $save
     *
     * @throws SortableException
     */
    public function move($action, $entity, $save = true)
    {
        $this->checkSortableGroupField(static::getSortableGroupField(), $entity);

        $this->_transaction(function () use ($entity, $action, $save) {
            $sortableField = static::getSortableField();

            $oldPosition = $this->getAttribute($sortableField);
            $newPosition = $entity->getAttribute($sortableField);

            if ($oldPosition === $newPosition) {
                return;
            }

            $isMoveBefore = $action === 'moveBefore'; // otherwise moveAfter
            $isMoveForward = $oldPosition < $newPosition;

            if ($isMoveForward) {
                $this->queryBetween($oldPosition, $newPosition)->decrement($sortableField);
            } else {
                $this->queryBetween($newPosition, $oldPosition)->increment($sortableField);
            }

            $this->setAttribute($sortableField, $this->getNewPosition($isMoveBefore, $isMoveForward, $newPosition));
            $entity->setAttribute($sortableField, $this->getNewPosition(! $isMoveBefore, $isMoveForward, $newPosition));

            if ($save) {
                $this->save();
            }
            $entity->save();
        });
    }

    /**
     * @return string|null
     */
    public function checkSortableGroupFieldChanged()
    {
        $dirty = false;

        $sortableGroupField = static::getSortableGroupField();

        if (! is_array($sortableGroupField)) {
            $sortableGroupField = [$sortableGroupField];
        }

        foreach ($sortableGroupField as $field) {
            if ($field !== null && $this->isDirty($field)) {
                $dirty = true;
            }
        }

        return $dirty;
    }

    /**
     * @param QueryBuilder        $query
     * @param Model|SortableTrait $model
     *
     * @return QueryBuilder
     */
    protected static function applySortableGroup($query, $model)
    {
        $query = $query->withTrashed();
        $sortableGroupField = static::getSortableGroupField();

        if (is_array($sortableGroupField)) {
            foreach ($sortableGroupField as $field) {
                $query = $query->where($field, $model->$field);
            }
        } elseif ($sortableGroupField !== null) {
            $query = $query->where($sortableGroupField, $model->$sortableGroupField);
        }

        return $query;
    }
}

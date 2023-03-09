<?php

/**
 * @desc  Policy Adapter
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/01/12 10:37
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission\Adapter;

use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use Casbin\Persist\UpdatableAdapter;
use Casbin\Persist\BatchAdapter;
use Casbin\Persist\FilteredAdapter;
use Casbin\Persist\Adapters\Filter;
use Casbin\Exceptions\InvalidFilterTypeException;
use Casbin\WebmanPermission\Model\LaravelRuleModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * DatabaseAdapter.
 *
 * @author techlee@qq.com
 */
class LaravelDatabaseAdapter implements Adapter, UpdatableAdapter, BatchAdapter, FilteredAdapter
{
    use AdapterHelper;

    /**
     * @var bool
     */
    private bool $filtered = false;

    /**
     * RuleModel model.
     *
     * @var LaravelRuleModel
     */
    protected LaravelRuleModel $model;

    /**
     * LaravelDatabaseAdapter constructor.
     *
     * @param  string|null  $driver
     */
    public function __construct(?string $driver = null)
    {
        $this->model = new LaravelRuleModel([],$driver);
    }

    /**
     * Filter the rule.
     *
     * @param array $rule
     * @return array
     */
    public function filterRule(array $rule): array
    {
        $rule = array_values($rule);

        $i = count($rule) - 1;
        for (; $i >= 0; $i--) {
            if ($rule[$i] != '' && !is_null($rule[$i])) {
                break;
            }
        }

        return array_slice($rule, 0, $i + 1);
    }

    /**
     * savePolicyLine function.
     *
     * @param string $ptype
     * @param array  $rule
     *
     * @return void
     */
    public function savePolicyLine(string $ptype, array $rule)
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . $key] = $value;
        }
        $this->model->updateOrCreate($col);
    }

    /**
     * loads all policy rules from the storage.
     *
     * @param Model $model
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->model->select(['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'])->get()->toArray();;
        foreach ($rows as $row) {
            $this->loadPolicyArray($this->filterRule($row), $model);
        }
    }

    /**
     * saves all policy rules to the storage.
     *
     * @param Model $model
     */
    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }
    }

    /**
     * adds a policy rule to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * Adds a policy rules to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[][] $rules
     */
    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        foreach ($rules as $rule) {
            $temp = ['ptype' => $ptype];
            foreach ($rule as $key => $value) {
                $temp['v' . $key] = $value;
            }
            $this->model->updateOrCreate($temp);
        }
    }

    /**
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $instance = $this->model->where('ptype', $ptype);
        foreach ($rule as $key => $value) {
            $instance->where('v' . $key, $value);
        }
        $data = $instance->get();
        foreach ($data as $item) {
            $item->delete();
        }
    }

    /**
     * @param string      $sec
     * @param string      $ptype
     * @param int         $fieldIndex
     * @param string|null ...$fieldValues
     * @return array
     * @throws Throwable
     */
    public function _removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, ?string ...$fieldValues): array
    {
        $removedRules = [];
        $data         = $this->getCollection($ptype, $fieldIndex, $fieldValues);

        foreach ($data as $model) {
            $item           = $model->hidden(['id', 'ptype'])->toArray();
            $item           = $this->filterRule($item);
            $removedRules[] = $item;
        }

        return $removedRules;
    }

    /**
     * Removes policy rules from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[][] $rules
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        DB::transaction(function () use ($sec, $ptype, $rules) {
            foreach ($rules as $rule) {
                $this->removePolicy($sec, $ptype, $rule);
            }
        });
    }

    /**
     * RemoveFilteredPolicy removes policy rules that match the filter from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param int    $fieldIndex
     * @param string ...$fieldValues
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $data = $this->getCollection($ptype, $fieldIndex, $fieldValues);
        foreach ($data as $item) {
            $item->delete();
        }
    }

    /**
     * Updates a policy rule from storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[] $oldRule
     * @param string[] $newPolicy
     */
    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newPolicy): void
    {
        $instance = $this->model->where('ptype', $ptype);
        foreach ($oldRule as $key => $value) {
            $instance->where('v' . $key, $value);
        }
        $instance = $instance->first();

        $update = [];
        foreach ($newPolicy as $key => $value) {
            $update['v' . $key] = $value;
        }

        $instance->fill($update);
        $instance->save();
    }

    /**
     * UpdatePolicies updates some policy rules to storage, like DB, redis.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[][] $oldRules
     * @param string[][] $newRules
     * @return void
     */
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        DB::transaction(function () use ($sec, $ptype, $oldRules, $newRules) {
            foreach ($oldRules as $i => $oldRule) {
                $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
            }
        });
    }

    /**
     * UpdateFilteredPolicies deletes old rules and adds new rules.
     *
     * @param string $sec
     * @param string $ptype
     * @param array $newPolicies
     * @param integer $fieldIndex
     * @param string ...$fieldValues
     * @return array
     */
    public function updateFilteredPolicies(string $sec, string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues): array
    {
        $oldRules = [];
        DB::transaction(function () use ($sec, $ptype, $fieldIndex, $fieldValues, $newPolicies, &$oldRules) {
            $oldRules = $this->_removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);
            $this->addPolicies($sec, $ptype, $newPolicies);
        });
        return $oldRules;
    }

    /**
     * Returns true if the loaded policy has been filtered.
     *
     * @return bool
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    /**
     * Sets filtered parameter.
     *
     * @param bool $filtered
     */
    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }

    /**
     * Loads only policy rules that match the filter.
     *
     * @param  Model  $model
     * @param  mixed  $filter
     *
     * @throws InvalidFilterTypeException
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        $instance = $this->model;
        if (is_string($filter)) {
            $instance->whereRaw($filter);
        }
        elseif ($filter instanceof Filter) {
            $where = [];
            foreach ($filter->p as $k => $v) {
                $where[$v] = $filter->g[$k];
            }
            $instance->where($where);
        }
        elseif ($filter instanceof Closure) {
            $instance = $instance->where($filter);
        }
        else {
            throw new InvalidFilterTypeException('invalid filter type');
        }
        $rows = $instance->get()->makeHidden(['created_at', 'updated_at', 'id'])->toArray();
        if ($rows) {
            foreach ($rows as $row) {
                $row  = array_filter($row, function ($value) {
                    return !is_null($value) && $value !== '';
                });
                $line = implode(
                    ', ',
                    array_filter($row, function ($val) {
                        return '' != $val && !is_null($val);
                    })
                );
                $this->loadPolicyLine(trim($line), $model);
            }
        }
        $this->setFiltered(true);
    }

    /**
     * @param  string  $ptype
     * @param  int     $fieldIndex
     * @param  array   $fieldValues
     *
     * @return Builder[]|Collection
     */
    protected function getCollection(string $ptype, int $fieldIndex, array $fieldValues) {
        $where = [
            'ptype' => $ptype,
        ];
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $where['v' . $value] = $fieldValues[$value - $fieldIndex];
                }
            }
        }

        return $this->model->where($where)->get();
    }
}
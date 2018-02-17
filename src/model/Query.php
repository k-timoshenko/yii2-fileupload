<?php
declare(strict_types=1);

namespace tkanstantsin\yii2fileupload\model;

use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class Query
 * @see File
 */
class Query extends ActiveQuery
{
    /* CONDITIONS */

    /**
     * @param array $idArray
     * @return Query
     */
    public function byIds(array $idArray): Query
    {
        if (\count($idArray) > 0) {
            $this->andWhere(['in', 'id', $idArray]);
        }

        return $this;
    }

    /**
     * @param string $alias
     * @param int $id
     * @param bool $addNull
     * @return Query
     */
    public function byModel(string $alias, int $id, bool $addNull = false): Query
    {
        $this->andWhere(['parent_model' => $alias]);
        if ($addNull) {
            $this->andWhere([
                'OR',
                ['parent_model_id' => $id],
                ['parent_model_id' => null],
            ]);
        } else {
            $this->andWhere(['parent_model_id' => $id]);
        }

        return $this;
    }

    /**
     * @param bool $isConfirmed
     * @return Query
     */
    public function isConfirmed(bool $isConfirmed = true): Query
    {
        return $this->andWhere(['is_confirmed' => $isConfirmed]);
    }

    /**
     * @param bool $isDeleted
     * @return Query
     */
    public function isDeleted(bool $isDeleted = true): Query
    {
        return $this->andWhere(['is_deleted' => $isDeleted]);
    }

    /**
     * @see File::isActual()
     * @return $this
     */
    public function isActual()
    {
        return $this->isConfirmed()
            ->isDeleted(false);
    }

    /**
     * @param int $maxAge seconds
     * @return $this
     */
    public function maxAge(int $maxAge = File::MAX_AGE): Query
    {
        return $this->andWhere([
            'OR',
            ['>', 'updated_at', new Expression('UNIX_TIMESTAMP() - ' . $maxAge)],
            ['is_confirmed' => true],
        ]);
    }

    /* ORDERS */

    /**
     * @param int $direction
     * @return Query
     */
    public function orderByPriority(int $direction = SORT_ASC): Query
    {
        return $this->orderBy([
            'priority IS NULL' => $direction,
            'priority' => $direction,
        ]);
    }
}
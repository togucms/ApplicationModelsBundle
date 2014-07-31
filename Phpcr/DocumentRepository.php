<?php

/*
 * Copyright (c) 2012-2014 Alessandro Siragusa <alessandro@togu.io>
 *
 * This file is part of the Togu CMS.
 *
 * Togu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Togu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Togu.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Togu\ApplicationModelsBundle\Phpcr;

use Doctrine\ODM\PHPCR\DocumentRepository as BaseDocumentRepository;
use Doctrine\ODM\PHPCR\Query\Builder\ConstraintFactory;

class DocumentRepository extends BaseDocumentRepository {
    /**
     * {@inheritDoc}
     */
	protected function constraintField(ConstraintFactory $where, $field, $value, $alias) {
		if(! is_array($value)) {
/*			$metadata = $this->getClassMetadata();
			$fieldMapping = $metadata->getField($field);
			$value = array($fieldMapping['defaultConstraint'] => $value);
*/			$value = array('eq' => $value);
		}
		foreach ($value as $constraint => $fieldValue) {
			$method = 'constraintField'. ucfirst($constraint);
			if(! method_exists($this, $method)) {
				throw new \InvalidArgumentException(sprintf('The repository %s has not the method %s', get_class($this), $method));
			}
			call_user_func_array(array($this, $method), array($where, $field, $fieldValue, $alias));
		}
	}

	protected function constraintFieldFieldIsset(ConstraintFactory $where, $field, $value, $alias) {
		$where->fieldIsset($alias.'.'.$field);
	}

	protected function constraintFieldFullTextSearch(ConstraintFactory $where, $field, $value, $alias) {
		$where->fullTextSearch($alias.'.'.$field, $value);
	}

	protected function constraintFieldSame(ConstraintFactory $where, $field, $value, $alias) {
		$where->same($value, $alias);
	}

	protected function constraintFieldDescendant(ConstraintFactory $where, $field, $value, $alias) {
		$where->descendant($value, $alias);
	}

	protected function constraintFieldChild(ConstraintFactory $where, $field, $value, $alias) {
		$where->child($value, $alias);
	}

	protected function constraintFieldEq(ConstraintFactory $where, $field, $value, $alias) {
		$where->eq()->field($alias.'.'.$field)->literal($value);
	}

	protected function constraintFieldNeq(ConstraintFactory $where, $field, $value, $alias) {
		$where->neq()->field($alias.'.'.$field)->literal($value);
	}

	protected function constraintFieldLt(ConstraintFactory $where, $field, $value, $alias) {
		$where->lt()->field($alias.'.'.$field)->literal($value);
	}

	protected function constraintFieldLte(ConstraintFactory $where, $field, $value, $alias) {
		$where->lte()->field($alias.'.'.$field)->literal($value);
	}

	protected function constraintFieldGt(ConstraintFactory $where, $field, $value, $alias) {
		$where->gt()->field($alias.'.'.$field)->literal($value);
	}

	protected function constraintFieldGte(ConstraintFactory $where, $field, $value, $alias) {
		$where->gte()->field($alias.'.'.$field)->literal($value);
	}

	protected function constraintFieldLike(ConstraintFactory $where, $field, $value, $alias) {
		$where->like()->field($alias.'.'.$field)->literal($value);
	}
}






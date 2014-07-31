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

namespace Togu\ApplicationModelsBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\ManagerEventArgs;
use Doctrine\ODM\PHPCR\Exception\InvalidChangesetException;

use Application\Togu\ApplicationModelsBundle\Document\Section;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use PHPCR\Util\NodeHelper;

class SectionConfig implements EventSubscriber
{
	protected $entities = array();
	protected $transactions = 0;

	public function getSubscribedEvents()
	{
		return array(
			'preFlush',
			'prePersist',
			'preUpdate',
			'preRemove',
			'endFlush'
		);
	}

	protected function updateRoute(LifecycleEventArgs $args) {
		$entity = $args->getEntity();

		if (! $entity instanceof Section || ! $entity->getLeaf() || ! $entity->getPage()) {
			return;
		}
		$route = $entity->getRoute();
		if($route === null || $route->getId() != $entity->getRoutePath()) {
			$this->entities[] = $entity;
		}
	}

	public function prePersist(LifecycleEventArgs $args) {
		$this->updateRoute($args);
	}

	public function preUpdate(LifecycleEventArgs $args) {
		$this->updateRoute($args);
	}

	public function preRemove(LifecycleEventArgs $args) {
		$entity = $args->getEntity();
		if (! $entity instanceof Section || ! $entity->getLeaf()) {
			return;
		}
		if($old = $entity->getRoute()) {
			$entityManager = $args->getObjectManager();
			if(count($old->getChildren()) <= 0) {
				$entityManager->remove($old);
			}
		}
	}

	public function preFlush(ManagerEventArgs $args) {
		if($this->transactions++ > 0) {
			return;
		}
		$entityManager = $args->getObjectManager();
		$workspace = $entityManager->getPhpcrSession()->getWorkspace();
		$utx = $workspace->getTransactionManager();
//		$utx->begin();
	}

	public function endFlush(ManagerEventArgs $args) {
		$entityManager = $args->getObjectManager();
		$needFlush = false;
//		$oldRoutes = array();
		foreach ($this->entities as $entity) {
			$needFlush = true;
			if($old = $entity->getRoute()) {
				$old->setContent($old);
//				$oldRoutes[] = $old;
			}

			$exploded = explode('/', $entity->getRoutePath());
			$name = array_pop($exploded);
			$parentPath = implode('/', $exploded);

			$parent = $entityManager->find(null, $parentPath);
			if(! $parent) {
				$session = $entityManager->getPhpcrSession();
				NodeHelper::createPath($session, $parentPath);
				$parent = $entityManager->find(null, $parentPath);
			}

			$route = $entityManager->find(null, $parentPath . "/" . $name);
			if(! $route) {
				$route = new Route(array('add_format_pattern' => true));
				$route->setPosition($parent, $name);
				$route->setRequirement('_format', 'html|json');
				$route->setDefault('type', 'default_type');
			}
			$route->setContent($entity->getPage());

			$entityManager->persist($route);

			$entity->setRoute($route);
		}
		$workspace = $entityManager->getPhpcrSession()->getWorkspace();
		$utx = $workspace->getTransactionManager();
		if($needFlush) {
			try {
				$this->entities = array();
				$entityManager->flush();
/*				foreach($oldRoutes as $old) {
					$old = $entityManager->find(null, $old->getId());
					if(count($old->getChildren()) <= 0) {
						$entityManager->remove($old);
					}
				}
				$entityManager->flush();
*/			} catch (\Exception $e) {
//				$utx->rollback();
				throw $e;
			}
		}
		if(-- $this->transactions == 0) {
//			$utx->commit();
		}
	}
}
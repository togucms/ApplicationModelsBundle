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

namespace Togu\ApplicationModelsBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use JMS\Serializer\Annotation as JMS;
use Togu\AnnotationBundle\Annotation as TOGU;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Application\Togu\ApplicationModelsBundle\Document\Page;

/**
 * @PHPCR\Document(referenceable=true)
 */
abstract class BaseSection
{
	/**
	 * @var integer
	 * @PHPCR\Id()
	 */
	protected $id;

	/**
	 *
	 * @JMS\Type("referenceone")
	 * @JMS\Groups({"front"})
	 * @JMS\Accessor(getter="getParentSection",setter="setParentSection")
	 * @JMS\SerializedName("parentSection")
	 * @var Model
	 * @PHPCR\ReferenceOne(strategy="hard")
	 */
	protected $parentSection;

	/**
	 * @JMS\Type("referenceone")
	 * @JMS\Groups({"list","put","post","patch","get"})
	 * @JMS\Accessor(getter="getParentSection",setter="setParentId")
	 * @JMS\SerializedName("parentId")
	 * @var Model
	 */
	protected $parentId;

	/**
	 *
	 * @JMS\Exclude
	 * @var Model
	 * @PHPCR\ReferenceMany(strategy="hard")
	 */
	protected $nextSection;

	/**
	 * @JMS\Exclude
	 * @PHPCR\ParentDocument()
	 */
	protected $parentDocument;

	/**
	 * @JMS\Groups({"list","put","post","patch","get"})
	 * @PHPCR\String()
	 */
	protected $text;

	/**
	 * @JMS\Groups({"list","put","post","patch","get"})
	 * @JMS\Accessor(getter="getLeaf",setter="setLeaf")
	 * @PHPCR\Boolean()
	 */
	protected $leaf;

	/**
	 * @JMS\Groups({"list","put","post","patch","get"})
	 * @JMS\Type("integer")
	 * @PHPCR\Long(nullable=true)
	 */
	protected $index;

	/**
	 * @JMS\Groups({"list","put","post","patch","get"})
	 * @PHPCR\String(nullable=true)
	 */
	protected $routePrefix;

	/**
	 * @JMS\Exclude
	 * @PHPCR\ReferenceOne(strategy="hard")
	 */
	protected $route;

	/**
	 * @JMS\Exclude
	 * @PHPCR\Child
	 */
	protected $page;

	/**
	 * @JMS\Groups({"list","put","post","patch","get"})
	 * @PHPCR\String
	 */
	protected $type;

	public function getRoutePath() {
		if($this->parentSection) {
			$prefix = $this->parentSection->getSectionConfig()->getRoutePath();
		} else {
			$prefix = "/cms/routes";
		}
		$prefix .= $this->getRoutePrefix();

		if($this->getLeaf()) {
			return rtrim($prefix . $this->text, '/');
		}

		return $prefix;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @param unknown $parent
	 */
	public function setParentDocument($parent) {
		$this->parentDocument = $parent;
		return $this;
	}

	/**
	 *
	 */
	public function getParentDocument() {
		return $this->parentDocument;
	}

	/**
	 * Set parentSection
	 *
	 * @param Model $parentSection
	 * @return Model
	 */
	public function setParentSection($parentSection)
	{
		if($this->parentSection !== $parentSection) {
			if($this->parentSection) {
				$this->parentSection->getSectionConfig()->removeNextSection($this->getParentDocument());
			}
			$this->parentSection = $parentSection;
			if($parentSection) {
				$parentSection->getSectionConfig()->addNextSection($this->getParentDocument());
			}
		}
		return $this;
	}

	/**
	 * Set parentId
	 *
	 * @param Model $parentSection
	 * @return Model
	 */
	public function setParentId($parentSection) {
//		$this->setParentSection($parentSection->getSectionConfig()->getParentDocument());
		$this->parentSection = $parentSection->getSectionConfig()->getParentDocument();
		return $this;
	}

	/**
	 * Get parentSection
	 *
	 * @return Model
	 */
	public function getParentSection()
	{
		return $this->parentSection;
	}

	/**
	 * Add nextSection
	 *
	 * @param $nextSection Model
	 * @return Model
	 */
	public function addNextSection(/*Model*/ $nextSection)
	{
		$this->nextSection[] = $nextSection;
		$nextSection->getSectionConfig()->setParentSection($this->getParentDocument());
		return $this;
	}

	/**
	 * Remove nextSection
	 *
	 * @param Model $nextSection
	 */
	public function removeNextSection($nextSection)
	{
		$this->sections->removeElement($nextSection);
	}

	/**
	 * Get nextSection
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getNextSection()
	{
		return $this->nextSection;
	}

	/**
	 * @param string $text
	 */
	public function setText($text) {
		$this->text = $text;
		return $this;
	}

	/**
	 *
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * @param boolean $leaf
	 */
	public function setLeaf($leaf) {
		$this->leaf = !! $leaf;
		if($this->leaf === true) {
			$this->getPage(true);
		}
		return $this;
	}

	/**
	 *
	 */
	public function getLeaf() {
		return $this->leaf;
	}


	/**
	 * @param integer $index
	 */
	public function setIndex($index) {
		$this->index = $index;
		return $this;
	}

	/**
	 *
	 */
	public function getIndex() {
		return $this->index;
	}


	/**
	 * @param Route $route
	 */
	public function setRoute(Route $route) {
		$this->route = $route;
		return $this;
	}

	/**
	 * @return Route
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * @param string $routePrefix
	 */
	public function setRoutePrefix($routePrefix) {
		$this->routePrefix = $routePrefix;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRoutePrefix() {
		return $this->routePrefix || "";
	}

	/**
	 * @param Page $page
	 */
	public function setPage(Page $page) {
		$this->page = $page;
		return $this;
	}

	/**
	 * @return Page
	 */
	public function getPage($createIfNull = false) {
		if($this->page === null && $createIfNull) {
			$page = new Page();
			$page->setTitle(' ');
			$page->setBody(' ');
			$page->setSection($this->getParentDocument());
			$this->setPage($page);
		}
		return $this->page;
	}


	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

}
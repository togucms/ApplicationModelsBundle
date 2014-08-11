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

use Symfony\Cmf\Bundle\ContentBundle\Doctrine\Phpcr\StaticContent;
use Symfony\Cmf\Bundle\SeoBundle\SeoAwareInterface;
use Symfony\Cmf\Bundle\SeoBundle\Doctrine\Phpcr\SeoMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Togu\AnnotationBundle\Annotation as TOGU;
use JMS\Serializer\Annotation as JMS;

/**
 * @PHPCR\Document(referenceable=true)
 */
abstract class BasePage extends StaticContent implements SeoAwareInterface
{

    /**
     * @JMS\Expose
     */
	protected $id;

    /**
     * @JMS\Expose
     * @JMS\Type("referenceone")
     * @TOGU\Type(type="referenceone")
	 * @PHPCR\ReferenceOne(strategy="hard")
     */
	protected $section;

	/**
	 * @var SeoMetadata
	 * @PHPCR\Child
	 */
	protected $seoMetadata;

    /**
     * @JMS\Expose
     */
	protected $title;

	/**
	 * @JMS\Expose
	 */
	protected $metaDescription;

	/**
	 * @JMS\Expose
	 */
	protected $metaKeywords;

	/**
	 * @JMS\Expose
	 */
	protected $url;

    /**
     * Constructor
     */
    public function __construct()
    {
       $this->seoMetadata = new SeoMetadata();
       parent::__construct();
    }

    /**
     * Set section
     *
     * @param $section
     * @return Page
     */
    public function setSection($section)
    {
        $this->section = $section;
        if($section) {
        	$section->getSectionConfig()->setPage($this);
        }

        return $this;
    }

    /**
     * Get section
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSection()
    {
    	return $this->section;
    }

    /**
     * Recursively gets all the sections
     *
     * @return array
     */
    public function getAllSections() {
    	$sections = array();
    	$section = $this->section;
    	do {
    		$sections[] = $section;
    	} while($section = $section->getSectionConfig()->getParentSection());

    	return array_reverse($sections);
    }


    /**
     * {@inheritDoc}
     */
    public function getSeoMetadata()
    {
    	return $this->seoMetadata;
    }

    /**
     * {@inheritDoc}
     */
    public function setSeoMetadata($seoMetadata)
    {
    	$this->seoMetadata = $seoMetadata;
    }

    /**
     *
     * @param string $title
     */
    public function setTitle($title) {
    	$this->title = $title;
    	$this->getSeoMetadata()->setTitle($title);
    }

    /**
     * @return string;
     */
    public function getTitle() {
    	return $this->getSeoMetadata()->getTitle();
    }

    /**
     *
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription) {
    	$this->getSeoMetadata()->setMetaDescription($metaDescription);
    }

    /**
     *
     * @return string
     */
    public function getMetaDescription() {
    	return $this->getSeoMetadata()->getMetaDescription();
    }

    /**
     *
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords) {
    	$this->getSeoMetadata()->setMetaKeywords($metaKeywords);
    }

    /**
     *
     * @return string
     */
    public function getMetaKeywords() {
    	return $this->getSeoMetadata()->getMetaKeywords();
    }

    /**
     *
     * @param string $url
     * @return \Togu\ApplicationModelsBundle\Document\BasePage
     */
    public function setUrl($url) {
    	$this->url = $url;
    	return $this;
    }

    /**
     * @return string
     */
    public function getUrl() {
    	return $this->url;
    }
}

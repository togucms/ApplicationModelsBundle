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

use Tpg\ExtjsBundle\Annotation as Extjs;
use JMS\Serializer\Annotation as JMS;
use Togu\AnnotationBundle\Annotation as TOGU;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Extjs\Model(name="Togu.applicationModels.Model")
 * Extjs\ModelProxy(option={"extraParams": {"entity": "contact"}}, name="entity")
 *
 * @PHPCR\Document(
 * 		referenceable=true,
 * 		versionable="simple",
 * 		translator="attribute"
 * )
 */
abstract class BaseModel {
    /**
     * @var integer
     * @PHPCR\Id()
     */
    protected $id;

    /**
     * @PHPCR\VersionName
     * @JMS\Exclude
     */
    protected $versionName;

    /**
     * @PHPCR\VersionCreated
     * @JMS\Exclude
     */
    protected $versionCreated;

    /**
     * @PHPCR\ParentDocument()
     * @JMS\Exclude
     */
    protected $parentDocument;


    /**
     * @PHPCR\String
     * @var string
     */
    protected $type;

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
    }

    /**
     *
     */
    public function getParentDocument() {
    	return $this->parentDocument;
    }


    /**
     * Initialize parentDocument
     */
    public function initParentDocument($params)
    {
    	if(isset($params['parentDocument'])) {
    		$this->setParentDocument($params['parentDocument']);
    	}
    }

    /**
     * @return string
     */
    public function getType() {
    	return $this->type;
    }

    /**
     *
     * @param string $type
     */
    public function setType($type) {
    	$this->type = $type;
    	return $this;
    }

    public function __construct($params = array()) {
		$this->initParentDocument($params);
    }
}

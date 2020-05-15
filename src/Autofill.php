<?php

namespace RobinMglsk;

use Exception;

class Autofill
{

    protected $pdfLink = null;
    protected $description = null;
    protected $modelHeight = 2;
    protected $modelWidth = 2;
    protected $pages = 1;
    protected $doubleSided = false;
    protected $bleed = 0;
    protected $grain = 'LL';
    protected $combination = false;
    protected $noProduction = false;
    protected $bindingType = 'Geen';
    protected $productionLines = [];
    protected $finishings = [];
    protected $packaging = [];
    protected $materials = [];
    protected $remark = '';


    public function __construct()
    {
    }

    // Setters

    /**
     * Set pdflink, valid ids can be found at /connector/internet/autoFillAttributes
     * @param int $pdfLink id of autofill object
     */
    public function setPdfLink(int $pdfLink): void
    {
        $this->pdfLink = $pdfLink;
    }

    /**
     * Set the description of this job. Is used for the checklist. If this attribute is not there is the description of the autofill attribute will be used.
     * @param string|null $desciption The description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Set the height of the model
     * @param int $height Height in mm
     */
    public function setHeight(int $height): void
    {
        if ($height > 0) {
            $this->modelHeight = $height;
        } else {
            throw new Exception('Height needs to be bigger than 0mm');
        }
    }

    /**
     * Set the height of the model
     * @param int $width Width in mm
     * */
    public function setWidth(int $width): void
    {
        if ($width > 0) {
            $this->modelWidth = $width;
        } else {
            throw new Exception('Width needs to be bigger than 0mm');
        }
    }

    /**
     * Set total number of pages
     * @param int $pages, number of pages. Has a value greater than 0.
     */
    public function setPages(int $pages): void
    {
        if ($pages > 0) {
            $this->pages = $pages;
        } else {
            throw new Exception('Needs more than 0 pages');
        }
    }

    /**
     * Set doublesided
     * @param bool $isDoubleSided
     */
    public function setDoubleSided(bool $isDoubleSided): void
    {
        $this->doubleSided = $isDoubleSided;
    }

    /**
     * Set bleed
     * @param int $bleed model bleed
     */
    public function setBleed(bool $bleed): void
    {
        $this->bleed = $bleed;
    }

    /**
     * Set model grain direction
     * @param string $grain Model grain direction (empty, LL or BL)
     */
    public function setGrain(string $grain = ''): void
    {
        $options = ['', 'LL', 'BL'];

        if (in_array($grain, $options)) {
            $this->grain = $grain;
        } else {
            throw new Exception('Invalid grain');
        }
    }

    /**
     * Set noproduction. Ensures that no production lines will be made by auto filling. 
     * @param bool $hasNoProduction
     */
    public function setNoproduction(bool $hasNoProduction): void
    {
        $this->hasNoProduction = $hasNoProduction;
    }

    /**
     * Set combination.
     * @param bool $isCombination
     */
    public function setCombination(bool $isCombination): void
    {
        $this->combination = $isCombination;
    }

    /**
     * Set combination.
     * @param bool $bindingType
     */
    public function setBindingType(bool $bindingType): void
    {
        $this->bindingType = $bindingType;
    }

    /**
     * Set remark.
     * @param string $remark
     */
    public function setRemark(bool $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * Add produciton line
     * @param string $method Fixed English term or the local language of production method (digital, largeformat, offset)
     * @param int $pages The number of pages for this line
     * @param string $material Material choice such as obtained through the GET of autofill attributes
     * @param string $colorlist  Color list selection as obtained through the GET of autofill attributes
     * 
     * @return int the id of the production line
     */
    public function addProductionLine(string $method, int $pages, string $material, string $colorlist): int
    {
        $productionLine = [
            'method' => $method,
            'current' => $pages,
            'material' => $material,
            'colorlist' => $colorlist,
        ];

        array_push($this->productionLines, $productionLine);

        return count($this->productionLines) - 1;
    }

    public function addFinishing(string $finishingType): int
    {
        array_push($this->finishings, ['description' => $finishingType]);
        return count($this->finishings) - 1;
    }

    /**
     * Add a material
     * @param int $productId Only the finish materials can be used where the id is from /stock/getArticleList?product_type=0&group_type=8
     * @param int $run1
     * @param int $run2
     * @param int $run3
     * 
     * @return int the id of the material
     */
    public function addMaterial(int $productId, int $run1 = 0, int $run2 = 0, int $run3 = 0): int
    {
        $material = [
            'product_id' => $productId,
            'run_01' => $run1,
            'run_02' => $run2,
            'run_03' => $run3,
        ];

        array_push($this->finishings, $material);
        return count($this->materials) - 1;
    }

    /**
     * Set packaging
     * @param int $type Needs to be filled with a item description from the packaging group. This data can be retrieved via /stock/getArticleList?product_type=0&group_type=5
     * @param int $run1
     * @param int $run2
     * @param int $run3
     * 
     * @return int the id of the material
     */
    public function setPackaging(string $type, int $run1 = 0, int $run2 = 0, int $run3 = 0): void
    {
    }

    // Getters 
    public function getProductionLines(): array
    {
        return $this->productionLines;
    }


    public function getProductionLineById($id): ?array
    {
        if (isset($this->productionLines[$id])) {
            return $this->productionLines[$id];
        } else {
            return null;
        }
    }

    //     finishing = here you can apply an array of finishes. (optional)
    //     materials = here you can apply an materials. (optional) These are then created in the finish tab. Only the finish materials can be used where the id is from/stock/getArticleList?product_type=0&group_type =8
    //     packaging = here you can add an object of packaging. (optional) This is especially useful for simple small orders that are produced in combination. Type needs to be filled with a item description from the packaging group. This data can be retrieved via/stock/getArticleList?product_type=0&group_type=5

    public function toArray()
    {
        $array = [
            'pdflink' => $this->pdfLink,
            'description' => $this->description,
            'modelheight' => $this->modelHeight,
            'modelwidth' => $this->modelWidth,
            'pages' => $this->pages,
            'doublesided' => $this->doubleSided,
            'bleed' => $this->bleed,
            'grain' => $this->grain,
            'combination' => $this->combination,
            'noproduction' => $this->noProduction,
            // 'bindingtype' => $this->bindingType,
            'productionlines' => $this->productionLines,
            'finishing' => $this->finishings,
            'packaging' => $this->packaging,
            'materials' => $this->materials,
            'remark' => $this->remark,
        ];

        return $array;
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray());
    }
}

<?php
namespace Itransition\DataImporterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Itransition\DataImporterBundle\Validator as ProductAsserts;

/**
 * Product
 *
 * @ORM\Table(name="tblProductData", uniqueConstraints={@ORM\UniqueConstraint(name="strproductcode", columns={"strproductcode"})})
 * @ORM\Entity
 * @ProductAsserts\PriceItem(conditions = {{5,10}, {1000}})
 */
class Product
{

    /**
     * @var integer
     *
     * @ORM\Column(name="intproductdataid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotNull()
     *
     * @ORM\Column(name="strproductname", type="string", length=50, nullable=false)
     */
    private $productName;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @ORM\Column(name="strproductdesc", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @ORM\Column(name="strproductcode", type="string", length=10, nullable=false)
     */
    private $code;

    /**
     * @var \DateTime
     * @ORM\Column(name="dtmadded", type="datetime", nullable=false)
     */
    private $dateAdded;

    /**
     * @var \DateTime
     * @ORM\Column(name="dtmdiscontinued", type="datetime", nullable=true)
     */
    private $discontinued;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stmtimestamp", type="datetime", nullable=false)
     */
    private $timeStamp;

    /**
     * @var integer
     *
     * @ProductAsserts\ProductItem
     *
     * @ORM\Column(name="intstock", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $stock;

    /**
     * @var float
     *
     * @ProductAsserts\ProductItem
     *
     * @ORM\Column(name="floatprice", type="decimal", scale=2, nullable=false, options={"unsigned"=true})
     */
    private $price;

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
     * Set productName
     *
     * @param string $productName
     *
     * @return Product
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Get productName
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Product
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     *
     * @return Product
     */
    public function setDateAdded(\DateTime $dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set discontinued
     *
     * @param \DateTime $discontinued
     *
     * @return Product
     */
    public function setDiscontinued(\DateTime $discontinued)
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    /**
     * Get discontinued
     *
     * @return \DateTime
     */
    public function getDiscontinued()
    {
        return $this->discontinued;
    }

    /**
     * Set timeStamp
     *
     * @param \DateTime $timeStamp
     *
     * @return Product
     */
    public function setTimeStamp(\DateTime $timeStamp)
    {
        $this->timeStamp = $timeStamp;

        return $this;
    }

    /**
     * Get timeStamp
     *
     * @return \DateTime
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * Set stock
     *
     * @param integer $stock
     *
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock
     *
     * @return integer
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }
}

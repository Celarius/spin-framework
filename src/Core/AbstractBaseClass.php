<?php declare(strict_types=1);
/**
 * Spin Framework
 *
 * Based on Hug code from https://github.com/dave1010/php-fig-psr-8
 *
 * @package   Spin
 */

namespace Spin\Core;

use \Psr\Hug\Huggable;
use \Psr\Hug\GroupHuggable;

/**
 *
 */
abstract class AbstractBaseClass implements Huggable, GroupHuggable
{
  /** @var \SplObjectStorage */
  private $hugList;

  /** @var boolean [description] */
  private $groupHugInitiated = false;


  /**
   * Constructor
   */
  public function __construct()
  {
    $this->hugList = new \SplObjectStorage();
  }


  /**
   * Hugs this object.
   *
   * All hugs are mutual. An object that is hugged MUST in turn hug the other
   * object back by calling hug() on the first parameter. All objects MUST
   * implement a mechanism to prevent an infinite loop of hugging.
   *
   * @param Huggable $friend
   *   The object that is hugging this object.
   *
   * @return self
   */
  public function hug(Huggable $h)
  {
    // Do not self-hug
    // Check if we are already hugging
    if ( $this != $h && !$this->hugList->contains($h) ) {
      // Add to list
      $this->hugList->attach($h);

      // Hug back
      $h->hug($this);
    }

    return $this;
  }


  /**
   * Hugs a series of huggable objects.
   *
   * When called, this object MUST invoke the hug() method of every object
   * provided. The order of the collection is not significant, and this object
   * MAY hug each of the objects in any order provided that all are hugged.
   *
   * @param $huggables
   *   An array or iterator of objects implementing the Huggable interface.
   *
   * @return self
   */
  public function groupHug($huggables)
  {
    if ($this->groupHugInitiated) {
      /**
       * We're mid way through a group hug. Don't initiate a new group hug with the same group
       */
      return $this;
    }

    $this->groupHugInitiated = true;

    $huggablesAndSelf = array_merge($huggables, [$this]);

    foreach ($huggables as $huggable)
    {
      if (!$huggable instanceof Huggable) {
        throw new \InvalidArgumentException("Can only hug Huggables");
      }

      if ($huggable instanceof GroupHuggable) {
        /**
         * Instruct $huggable to join in the whole group hug, instead of just us.
         */
        $huggable->groupHug(array_filter($huggablesAndSelf, function($h) use ($huggable) {
            // don't get $huggable to hug itself
            return $h !== $huggable;
        }));
      } else {
          /**
           * We have the ability to group hug but $huggable does not.
           */
          $this->hug($huggable);
      }
    }
    $this->groupHugInitiated = false;
  }

}

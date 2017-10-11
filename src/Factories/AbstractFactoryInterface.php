<?php declare(strict_types=1);

namespace Spin\Factories;

interface AbstractFactoryInterface
{
  /**
   * @return mixed
   */
  function getOptions(): array;

  /**
   * @param mixed $options
   *
   * @return self
   */
  function setOptions(array $options);
}

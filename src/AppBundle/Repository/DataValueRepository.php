<?php

namespace AppBundle\Repository;

use AppBundle\Entity\DataValue;
use AppBundle\Entity\FeedData;
use Doctrine\ORM\QueryBuilder;

/**
 * DataValueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DataValueRepository extends \Doctrine\ORM\EntityRepository
{
  /**
   * Get an average value
   *
   * @param \DateTime $startDate
   * @param \DateTime $endDate
   * @param int $frequency
   */
  public function getAverageValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency)
  {
      // Create the query builder
      $queryBuilder = $this->createQueryBuilder('d');

      $queryBuilder->select('AVG(d.value) AS value');
      $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
      $queryBuilder->groupBy('d.id');
      return $queryBuilder
          ->getQuery()
          ->getScalarResult();
  }

  /**
   * Get sum of value
   *
   * @param \DateTime $startDate
   * @param \DateTime $endDate
   * @param string $frequency
   */
  public function getSumValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency)
  {
      // Create the query builder
      $queryBuilder = $this->createQueryBuilder('d');

      $queryBuilder->select('SUM(d.value) AS value');
      $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
      $queryBuilder->groupBy('d.id');

      return $queryBuilder
          ->getQuery()
          ->getScalarResult();
  }

  /**
   * Get value
   *
   * @param \DateTime $startDate
   * @param \DateTime $endDate
   * @param string $frequency
   */
  public function getValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency)
  {
      // Create the query builder
      $queryBuilder = $this->createQueryBuilder('d');

      $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);

      return $queryBuilder
          ->getQuery()
          ->getResult();
  }

  /**
   * Get repartition
   *
   * @param \DateTime $startDate
   * @param \DateTime $endDate
   * @param string $frequency
   */
  public function getRepartitionValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $repartitionType)
  {
      if ($repartitionType === 'WEEK') {
          $axeX = 'week_day';
          $axeY = 'hour';
          $frequency = DataValue::FREQUENCY['HOUR'];
      }
      elseif ($repartitionType === 'YEAR') {
          $axeX = 'week';
          $axeY = 'week_day';
          $frequency = DataValue::FREQUENCY['DAY'];
      }
      else {
        return NULL;
      }

      // Create the query builder
      $queryBuilder = $this->createQueryBuilder('d');

      $queryBuilder->select('AVG(d.value) value, d.' . $axeX . ', d.' . $axeY . '');
      $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
      $queryBuilder->groupBy('d.' . $axeX);
      $queryBuilder->groupBy('d.' . $axeY);

      return $queryBuilder
          ->getQuery()
          ->getResult() ;
  }

  public function betweenDateWithFeedDataAndFrequency(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency, QueryBuilder &$queryBuilder)
  {
      $queryBuilder
          ->andWhere('d.date BETWEEN :start AND :end')
          ->setParameter('start', $startDate)
          ->setParameter('end',   $endDate)
          // Add condition on feedData
          ->andWhere('d.feedData = :feedData')
          ->setParameter('feedData', $feedData->getId())
          // Add condition on frequency
        ->andWhere('d.frequency = :frequency')
        ->setParameter('frequency', $frequency);
  }
}

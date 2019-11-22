<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

abstract class AbstractRepository extends ServiceEntityRepository
{
	protected function paginate(QueryBuilder $qb, $limit = 10, $page = 1)
	{
		if (0 >= $limit || 0 >= $page) {
			throw new \LogicException('page must be greater than 0.');
		}

		$pager = new Pagerfanta(new DoctrineORMAdapter($qb));
		if (!is_numeric($page))
			$page = 1;
		if (!is_numeric($limit))
			$limit = 12;
		if ($limit > 50)
			$limit = 50;
		$pager->setCurrentPage($page);
		$pager->setMaxPerPage((int) $limit);

		return $pager;
	}

	public function findProblematic($page = 1, $limit = 12, $orderBy = null, $order = null)
	{
		$qb = $this->createQueryBuilder('s')
			->select('s')
			->addSelect('(SELECT COALESCE(SUM(v.good) - (COUNT(v.good) - SUM(v.good)),0)
				   FROM \App\Entity\Vote v
				   WHERE v.problematic = s.id) as votes');
		
		$option = 'DESC';
		if (null !== $order && $order === 'ASC')
			$option = 'ASC';

		if (null !== $orderBy && $orderBy === 'DATE') {
			$qb->orderBy('s.creationDate', $option)
			->addOrderBy('votes', $option);
		} else {
			$qb->orderBy('votes', $option)
			->addOrderBy('s.creationDate', $option);
		}

		return $this->paginate($qb, $limit, $page);
	}

	public function filterProblematic($page = 1, $limit = 12, $orderBy = null, $order = null, $searchers = null, $categories = null, $subCategories = null, $keywords = null)
	{
		$qb = $this->createQueryBuilder('s')
			->select('s')
			->addSelect('(SELECT COALESCE(SUM(v.good) - (COUNT(v.good) - SUM(v.good)),0)
				   FROM \App\Entity\Vote v
				   WHERE v.problematic = s.id) as votes')
			->innerJoin('s.owner','o')
			->innerJoin('s.category', 'c');
		
		if (null !== $searchers)
			$qb->where($qb->expr()->in('o.uuid', ':searchers'))
				->setParameter('searchers', $searchers);
		if (null !== $categories)
			$qb->andWhere($qb->expr()->in('c.category', ':categories'))
				->setParameter('categories', $categories);
		if (null !== $subCategories)
			$qb->andWhere($qb->expr()->in('s.category', ':subCategories'))
				->setParameter('subCategories', $subCategories);
		if (null !== $keywords) {
			foreach($keywords as $keyword){
				$qb->AndWhere('s.keywords LIKE :keyword')
					->setParameter('keyword', '%'.$keyword.'%');          
			}
		}

		$option = 'DESC';
		if (null !== $order && $order === 'ASC')
			$option = 'ASC';

		if (null !== $orderBy && $orderBy === 'DATE') {
			$qb->orderBy('s.creationDate', $option)
			->addOrderBy('votes', $option);
		} else {
			$qb->orderBy('votes', $option)
			->addOrderBy('s.creationDate', $option);
		}

		return $this->paginate($qb, $limit, $page);
	}

	public function findComments($page = 1, $limit = 10, $problematic)
	{
		$qb = $this->createQueryBuilder('s')
			->select('s')
			->addSelect('(SELECT COALESCE(SUM(v.good) - (COUNT(v.good) - SUM(v.good)),0)
				   FROM \App\Entity\Vote v
				   WHERE v.comment = s.id) as votes');

		$qb->where('s.problematic = ?1')
				->setParameter(1, $problematic)
				->orderBy('votes', 'DESC')
				->addOrderBy('s.creationDate', 'DESC');
		return $this->paginate($qb, $limit, $page);
	}
}

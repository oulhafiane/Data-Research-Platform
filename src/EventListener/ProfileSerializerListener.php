<?php

namespace App\EventListener;

use App\Entity\Searcher;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;

class ProfileSerializerListener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array(
			array(
				'event' => 'serializer.post_serialize',
				'method' => 'onPostSerialize',
				'class' => 'App\Entity\Searcher', // if no class, subscribe to every serialization
				'format' => 'json', // optional format
				'priority' => 0, // optional priority
			)
		);
	}

	public function onPostSerialize(ObjectEvent $event)
	{
		$profile = $event->getObject();
		if ($profile instanceof Searcher) {
			$countProblematics = count($profile->getProblematics());
			$countComments = count($profile->getComments());
			$visitor = $event->getVisitor();
			$visitor->visitProperty(new StaticPropertyMetadata('App\Entity\Searcher', 'countProblematics', null), $countProblematics);
			$visitor->visitProperty(new StaticPropertyMetadata('App\Entity\Searcher', 'countComments', null), $countComments);
		}
	}
}

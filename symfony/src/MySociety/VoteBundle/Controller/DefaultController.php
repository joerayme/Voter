<?php
namespace MySociety\VoteBundle\Controller;

use Symfony\Component\Validator\Constraints\Collection;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MySociety\VoteBundle\Entity\Voting;
use Doctrine\ORM\EntityRepository;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $params = array();
        $voting = new Voting($this->container->get('snc_redis.default_client'));
        
        $params['votes']  = $voting->getVotersCount();
        $params['voted']  = $voting->hasVoted();
        
        return $this->render('MySocietyVoteBundle:Default:index.html.twig', $params);
    }
    
    public function resultsAction($constituency = null)
    {
        $params = array();
        $voting = new Voting($this->container->get('snc_redis.default_client'));
        
        $params['votes']  = $voting->getVotersCount();
        $params['voting'] = $voting->getVotersCount('voting', 1);
        $params['voted']  = $voting->hasVoted();
            
        $params['constituencies'] = $this->getDoctrine()
            ->getRepository('MySocietyVoteBundle:Constituency')
            ->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()->getResult();
        
        if ($constituency != null)
        {
            $params['c'] = $this->getDoctrine()
                ->getRepository('MySocietyVoteBundle:Constituency')
                ->findOneById($constituency);
            
            if (!$params['c']) {
                throw $this->createNotFoundException('The constituency does not exist');
            }
            
            $parties = $this->getDoctrine()->getRepository('MySocietyVoteBundle:Party')
                ->createQueryBuilder('p')
                ->orderBy('p.name', 'ASC')
                ->getQuery()->getResult();
            
            foreach ($parties as $i => $p)
            {
                $p->setVotes($voting->getVotersCount('constituency', $constituency, 'party', $p->getId()));
            }
            
            $params['parties']   = $parties;
            $params['undecided'] = $voting->getVotersCount('constituency', $constituency, 'party', 0);
            $params['votersByConstituency'] = $voting->getVotersCount('constituency', $constituency);
        }
        else
        {
            
            $parties = $this->getDoctrine()->getRepository('MySocietyVoteBundle:Party')
                ->createQueryBuilder('p')
                ->orderBy('p.name', 'ASC')
                ->getQuery()->getResult();
            
            foreach ($parties as $i => $p)
            {
                $p->setVotes($voting->getVotersCount('party', $p->getId()));
            }
            
            $params['parties'] = $parties;
            $params['undecided'] = $voting->getVotersCount('party', 0);
        }
        
        return $this->render('MySocietyVoteBundle:Default:results.html.twig', $params);
    }
    
    public function voteAction(Request $request)
    {
        $voting = new Voting($this->container->get('snc_redis.default_client'));
        
        $this->get('logger')->info('Has voted: ' . var_export($voting->hasVoted(), true));
        
        if ($voting->hasVoted())
        {
            return $this->redirect($this->get('router')->generate('MySocietyVoteBundle_homepage'));
        }
        
        $params = array();
        
        $form = $this->createFormBuilder()
            ->add('constituency', 'entity', array(
                'class'        => 'MySocietyVoteBundle:Constituency',
                'required'     => true,
                'multiple'     => false,
                'expanded'     => false,
                'label'        => 'Which constituency are you in?',
                'query_builder'=> function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ))
            ->add('voting', 'choice', array(
                'choices' => array(
                    1 => 'Yes',
                    0 => 'No',
                ),
                'required'     => true,
                'multiple'     => false,
                'expanded'     => true,
                'label'        => 'Will you be voting?',
            ))
            ->add('party', 'entity', array(
                'class'        => 'MySocietyVoteBundle:Party',
                'required'     => false,
                'multiple'     => false,
                'expanded'     => false,
                'label'        => 'Who do you intend to vote for?',
                'query_builder'=> function(EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'empty_value'  => 'Undecided',
            ))
            ->getForm();

        if ($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
            
            $data = $form->getData();
            
            if ($data['voting'] == 1) {
                $pid = is_object($data['party']) ? $data['party']->getId() : 0;
                $votes = array(
                    array('voting', 1),
                    array('constituency', $data['constituency']->getId()),
                    array('constituency', $data['constituency']->getId(), 'party', $pid),
                    array('party', $pid),
                );
            } else {
                $votes = array(
                    array('constituency', $data['constituency']->getId()),
                    array('voting' => 0),
                );
            }
            $voting->vote($votes);
            
            $params['voted'] = true;
        } else {
            $params['voted'] = false;
            $params['form']  = $form->createView();
        }
        
        return $this->render('MySocietyVoteBundle:Default:vote.html.twig', $params);
    }
}

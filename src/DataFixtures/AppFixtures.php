<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Product;
use function Symfony\Component\String\u;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $slugger;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, SluggerInterface $slugger)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadProducts($manager);
    }

    private function loadUsers(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$fullname, $username, $password, $email, $roles]) {
            $user = new User();
            $user->setFullName($fullname);
            $user->setUsername($username);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($roles);

            $manager->persist($user);
            $this->addReference($username, $user);
        }

        $manager->flush();
    }  

    private function loadProducts(ObjectManager $manager): void
    {
        foreach ($this->getProductData() as [$name, $slug, $summary, $content, $price, $imageUrl ,$publishedAt, $author]) {
            $product = new Product();
            $product->setName($name);
            $product->setSlug($slug);
            $product->setSummary($summary);
            $product->setContent($content);
            $product->setPrice($price);
            $product->setPublishedAt($publishedAt);
            $product->setAuthor($author);

            foreach (range(1, 5) as $i) {
                $comment = new Comment();
                $comment->setAuthor($this->getReference('vandana_user'));
                $comment->setContent($this->getRandomProductText(random_int(255, 512)));
                $comment->setPublishedAt(new \DateTime('now + '.$i.'seconds'));

                $product->addComment($comment);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            // $userData = [$fullname, $username, $password, $email, $roles];
            ['Vandana Silgari', 'vandana_admin', 'vandana@admin', 'vandana_admin@gmail.com', ['ROLE_ADMIN']],
            ['Vandana Sam', 'sam_admin', 'sam@admin', 'sam_admin@gmail.com', ['ROLE_ADMIN']],
            ['Vandana Tora', 'vandana_user', 'vandana@user', 'vandana_user@gmail.com', ['ROLE_USER']],
        ];
    }

    private function getProductData()
    {
        $products = [];
        foreach ($this->getProducts() as $i => $name) {
 // $postData = [$name, $slug, $summary, $content, $price, $imageUrl , $publishedAt, $author, $comments];
            $products[] = [
                $name,
                $this->slugger->slug($name)->lower(),
                $this->getRandomProductText(),
                $this->getProductContent(),
                $this->getProductPrices($i),
                $this->getProductImageUrl($i),
                new \DateTime('now - '.$i.'days'),
                // Ensure that the first post is written by Vandana Silgari to simplify tests
                $this->getReference(['vandana_admin', 'sam_admin'][0 === $i ? 0 : random_int(0, 1)])               
            ];
        }

        return $products;
    }

    private function getProducts(): array
    {
        return [
            'BSNL Introduces Rs 2 Prepaid Plan Extension Offer, All You Should Know',
            'Vodafone Idea Rs 251 Data Voucher to Come With 28 Days Validity',
            'Bharti Airtel Rs 251 Prepaid Data Pack is Now Available for Recharge via Official Channels',
            'Google Might Team Up with Vodafone Idea to Pit Against Facebook-Jio Behemoth',
            'Walmart Not Surprised with Facebook Investment into Reliance Jio',
            'BSNL Introduces Rs 2 Prepaid Plan Extension Offer',
            'Vodafone Idea Rs 251 Data Voucher',
            'Bharti Airtel Rs 251 Prepaid Data Pack is Now',
            'Google Might Team Up with Vodafone Idea',
            'Walmart Not Surprised with Facebook ',                          
        ];
    }

    
    private function getProductPrices($index): string
    {
        $prices =  [
            '400000',
            '600000',
            '700000',
            '300000',
            '900000',
            '400000',
            '600000',
            '700000',
            '300000',
            '900000',         
        ];
        return $prices[$index];

    }
    
    private function getProductImageUrl($index): string
    {
        $imageUrls =  [
            'https://telecomtalk.info/wp-content/uploads/2020/05/bsnl-rs2-prepaid-plan-extension-you-should-know.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/vodafone-idea-rs251-data-voucher-come-28days-validity-340x200.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/bharti-airtel-rs251-data-pack-official-channels-1024x634-340x200.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/vodafone-idea-rs251-data-voucher-come-28days-validity-340x200.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/bsnl-bharat-fiber-broadband-free-service-340x200.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/bsnl-rs2-prepaid-plan-extension-you-should-know.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/vodafone-idea-rs251-data-voucher-come-28days-validity-340x200.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/bharti-airtel-rs251-data-pack-official-channels-1024x634-340x200.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/vodafone-idea-rs251-data-voucher-come-28days-validity-340x200.jpg',
            'https://telecomtalk.info/wp-content/uploads/2020/05/bsnl-bharat-fiber-broadband-free-service-340x200.jpg'
        ];
        return $imageUrls[$index];

    }

    private function getRandomProductText(int $maxLength = 255): string
    {
        $products = $this->getProducts();
        shuffle($products);

        do {
            $text = u('. ')->join($products)->append('.');
            array_pop($products);
        } while ($text->length() > $maxLength);

        return $text;
    }

  

    private function getProductContent(): string
    {
        return <<<'MARKDOWN'

BSNL has been impressive with launching new plans for its various services at a rapid pace.
The state-owned telco earlier allowed its users to pay Rs 19 and extend the validity of 
their plan. Now, the telco is out with yet another offer of Rs 2 which will allow the users to extend
the validity of their existing prepaid plan. Isn’t that a treat for customers 
who for some reason can’t recharge with a new prepaid plan as soon as the validity of their existing plan ends.
But the extension is not unlimited obviously, there is a certain grace period for the extension of validity.

Rs 2 Prepaid Plan Extension from BSNL

The latest offer from BSNL coming for Rs 2 will allow the customers to extend the validity of 
their existing plan. The grace period for the extension of validity is three days. However, 
it is important to note that there are no other benefits offered apart from this. The new offer 
has been announced in the telecom circle of Tami Nadu. But don’t worry if you are not living in 
the Tamil Nadu, you can still get this offer. BSNL has availed the Rs 2 prepaid plan extension 
offer for every telecom circle it provides its services. So if on the last day of the validity 
of your plan, you recharge your number with Rs 2, you will get a relief of three more days to 
get a new prepaid plan for yourself.

Rs 19 Prepaid Plan Extension from BSNL

This is yet another prepaid plan extension offer from BSNL. The Rs 19 prepaid plan extension gave
users the benefit of extending their existing plan by 30 days. Again, there no other benefits 
except the extension of the existing plan. So after paying Rs 19, users got a relief of 30 days to
recharge their number with a new prepaid plan.

MARKDOWN;
    }
}

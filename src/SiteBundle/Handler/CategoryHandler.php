<?php

namespace SiteBundle\Handler;


use AdminBundle\Services\ImageService;
use Doctrine\ORM\EntityManager;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\Category;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Services\ServiceContainer;
use SiteBundle\Services\UrlService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CategoryHandler extends ServiceContainer
{
    protected $categoryDir;
    protected $img;
    protected $urlService;

    public function __construct(EntityManager $entity, TokenStorage $tokenStorage, $categoryDir, ImageService $imageService, UrlService $urlService)
    {
        parent::__construct($entity, $tokenStorage);

        $this->categoryDir = $categoryDir;
        $this->img = $imageService;
        $this->urlService = $urlService;
    }

    /**
     * Insert or update badge
     * @param array $data
     * @param null $id
     * @return bool|string
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function setCategory(array $data, $id = null)
    {
        if(empty(array_filter($data)))
            throw new BadRequestHttpException(MessageConstants::EMPTY_REQUEST);

        $this->em->beginTransaction();
        try{
            if(null === $id)
                $this->insertCategory($data);
            else
                $this->updateCategory($data, $id);

            $this->em->commit();
            return true;
        }catch (\Exception $exception){
            $this->em->rollback();
            return $exception->getMessage();
        }
    }

    /**
     * Delete Category from Db
     * @param $id
     * @return bool
     * @throws \SiteBundle\Exceptions\ApplicationException
     */
    public function deleteCategory($id)
    {
        /** @var Category $category */
        $category = $this->em->getRepository('SiteBundle:Category')->find($id);

        if(null === $category)
            throw new ApplicationException(MessageConstants::NOT_FOUND);

        if(null !== $category->getImage()) {
            $this->img->deleteImageFromDir("{$this->categoryDir}/{$category->getImage()}");
        }

        if($category->getAdsids()->count() > 0){
            $category->setIsdeleted(true);
            $this->updateData($category);
        }else {
            $this->removeData($category);
        }

        return true;
    }

    /**
     * Insert Category
     * @param $data
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \PDOException
     */
    private function insertCategory($data)
    {
        $data['Alias'] = $this->urlService->generateSeoUrl($data['Name']);
        $data['Parent'] = $data['ParentId'];
        $data['isDeleted'] = 0;
        $category = $this->em->getRepository('SiteBundle:Category')->getByAlias($data['Alias']);

        if(null !== $category )
            throw new \PDOException(MessageConstants::EXIST);

        $this->img->setImageToFileSystem($data['Documents'], $this->categoryDir, false, 'category');
        /** @var Category $category */
        $category = $this->arrayToEntity($data, 'SiteBundle:Category');

        if(!empty($data['Documents'])) {
            $category->setImage($data['Documents'][0]['FileName']);
        }

//        $errors = $this->validator->validate($purchase);

        //dump(json_encode($errors)); exit;
//        if(count($errors) > 0)
//            throw new ValidationException($errors);


        $this->insertData($category);
    }

    /**
     * Update Category
     * @param $data
     * @param $id
     * @throws \PDOException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateCategory($data, $id)
    {
        $id = (int)$id;

        if( empty($id) )
            throw new \PDOException(MessageConstants::BADGE_ID_NOT_EXIST);

        $data['Alias'] = $this->urlService->generateSeoUrl($data['Name']);
        $data['Parent'] = $data['ParentId'];
        $category = $this->em->getRepository('SiteBundle:Category')->getByAlias($data['Alias'], $id);

        if(null !== $category )
            throw new \PDOException(MessageConstants::EXIST);

        $categoryObj = $this->em->getRepository('SiteBundle:Category')->find($id);

        if(null === $categoryObj)
            throw new \PDOException(MessageConstants::BADGE_ID_NOT_EXIST);

        $this->img->setImageToFileSystem($data['Documents'], $this->categoryDir, true, 'category');

        $data['id'] = $id;
        /** @var Category $category */
        $category = $this->arrayToEntity($data, 'SiteBundle:Category');

        if(!empty($data['Documents'])) {
            $category->setImage(count($data['Documents']) > 1 ? $data['Documents'][1]['FileName'] : $data['Documents'][0]['FileName']);
        }


//        $errors = $this->validator->validate($purchase);

        //dump(json_encode($errors)); exit;
//        if(count($errors) > 0)
//            throw new ValidationException($errors);

        $this->updateData($category);
    }
}
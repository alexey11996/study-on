<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\DataFixtures\CourseFixtures​;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use App\Entity\Course;

class LessonControllerTest extends AbstractTest
{
    public function getFixtures(): array
    {
        return [CourseFixtures​::class];
    }

    public function authClient($email, $password)
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->getContainer()->set('App\Service\BillingClient', new BillingClientMock($_ENV['BILLING_HOST']));
        $client->request('GET', '/courses/');
        $crawler = $client->clickLink('Вход');
        $form = $crawler->selectButton('Войти')->form();
        $form["email"] = $email;
        $form["password"] = $password;
        $client->submit($form);
        $client->followRedirect();
        return $client;
    }

    public function testNewResponse()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $client->clickLink('Пройти курс');
        $client->clickLink('Добавить урок');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testShowResponse()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $crawler = $client->clickLink('Пройти курс');
        $link = $crawler->filter('.lessonShow')->first();
        $client->clickLink($link->text());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEditResponse()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $crawler = $client->clickLink('Пройти курс');
        $link = $crawler->filter('.lessonShow')->first()->text();
        $client->clickLink($link);
        $client->clickLink('Редактировать');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCountLessons()
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->getContainer()->set('App\Service\BillingClient', new BillingClientMock($_ENV['BILLING_HOST']));
        $client->request('GET', '/courses/');
        $crawler = $client->clickLink('Пройти курс');
        $this->assertEquals(1, $crawler->filter('.lessonShow')->count());
    }

    public function testLesson404()
    {
        $client = static::createClient();
        $client->request('GET', '/lessons/25');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testLessonEdit404()
    {
        $client = static::createClient();
        $client->request('GET', '/lessons/25/edit');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testLessonAdd()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $client->clickLink('Пройти курс');
        $crawler = $client->clickLink('Добавить урок');
        $form = $crawler->selectButton('Сохранить')->form();
        $form["lesson[name]"] = "Новый урок";
        $form["lesson[content]"] = "Описание нового урока";
        $form["lesson[serialNumber]"] = 5;
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals(2, $crawler->filter('.lessonShow')->count());
    }

    public function testLessonEdit()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $crawler = $client->clickLink('Пройти курс');
        $link = $crawler->filter('a')->eq(3);
        $client->clickLink($link->text());
        $crawler = $client->clickLink('Редактировать');
        $form = $crawler->selectButton('Сохранить')->form();
        $form["lesson[name]"] = "Новый урок";
        $form["lesson[content]"] = "Описание нового урока!!!";
        $form["lesson[serialNumber]"] = 5;
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Описание нового урока!!!")')->count() > 0);
    }

    public function testLessonDelete()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $crawler = $client->clickLink('Пройти курс');
        $link = $crawler->filter('a')->eq(3);
        $crawler = $client->clickLink($link->text());
        $form = $crawler->selectButton('Удалить')->form();
        $crawler = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("В данном курсе нет ни одного урока")')->count() > 0);
    }

    public function testLessonAddWithWrongNumber()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $client->clickLink('Пройти курс');
        $crawler = $client->clickLink('Добавить урок');
        $form = $crawler->selectButton('Сохранить')->form();
        $form["lesson[name]"] = "Новый урок";
        $form["lesson[content]"] = "Описание нового урока";
        $form["lesson[serialNumber]"] = 10000;
        $crawler = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Введите число меньше 1000!")')->count() > 0);
    }

    public function testLessonAddWithBlankName()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $client->clickLink('Пройти курс');
        $crawler = $client->clickLink('Добавить урок');
        $form = $crawler->selectButton('Сохранить')->form();
        $form["lesson[name]"] = "";
        $form["lesson[content]"] = "Описание нового урока";
        $form["lesson[serialNumber]"] = 10;
        $crawler = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("This value should not be blank")')->count() > 0);
    }

    public function testLessonAddWithBlankContent()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $client->clickLink('Пройти курс');
        $crawler = $client->clickLink('Добавить урок');
        $form = $crawler->selectButton('Сохранить')->form();
        $form["lesson[name]"] = "Новый урок";
        $form["lesson[content]"] = "";
        $form["lesson[serialNumber]"] = 10;
        $crawler = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("This value should not be blank")')->count() > 0);
    }

    public function testLessonAddWithBlankNumber()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $client->clickLink('Пройти курс');
        $crawler = $client->clickLink('Добавить урок');
        $form = $crawler->selectButton('Сохранить')->form();
        $form["lesson[name]"] = "Новый урок";
        $form["lesson[content]"] = "Описание нового урока";
        $form["lesson[serialNumber]"] = "";
        $crawler = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("This value should not be blank")')->count() > 0);
    }

    public function testLessonsOrderBy()
    {
        $client = $this->authClient('adminUser@gmail.com', 'passwordForAdminUser');
        $client->clickLink('Пройти курс');
        $crawler = $client->clickLink('Добавить урок');
        $form = $crawler->selectButton('Сохранить')->form();
        $form["lesson[name]"] = "Новый урок";
        $form["lesson[content]"] = "Описание нового урока";
        $form["lesson[serialNumber]"] = 10;
        $client->submit($form);
        $crawler = $client->followRedirect();
        $form["lesson[name]"] = "Еще один новый урок";
        $form["lesson[content]"] = "Описание еще одного нового урока";
        $form["lesson[serialNumber]"] = 8;
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals("Еще один новый урок", $crawler->filter('a')->eq(4)->text());
    }

    public function testAnonymousAddLesson()
    {
        $client = static::createClient();
        $client->request('GET', '/lessons/new');
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
    }

    public function testAnonymousLessonShow()
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->getContainer()->set('App\Service\BillingClient', new BillingClientMock($_ENV['BILLING_HOST']));
        $client->request('GET', '/courses/');
        $crawler = $client->clickLink('Пройти курс');
        $link = $crawler->filter('.lessonShow')->first();
        $client->clickLink($link->text());
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
    }

    public function testLoggedInUserAddLesson()
    {
        $client = $this->authClient('simpleUser@gmail.com', 'passwordForSimpleUser');
        $crawler = $client->request('GET', '/lessons/new');
        $this->assertTrue($crawler->filter('html:contains("Доступ запрещен!")')->count() > 0);
    }
}

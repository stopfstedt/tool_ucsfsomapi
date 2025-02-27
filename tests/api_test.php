<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test coverage for UCSF SOM API web services class.
 *
 * @package    tool_ucsfsomapi
 * @copyright  The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_ucsfsomapi;

use context_module;
use core_external\external_api;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\util;
use externallib_advanced_testcase;
use question_bank;
use tool_ucsfsomapi\external\api;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Test coverage for UCSF SOM API web services class.
 *
 * @covers \tool_ucsfsomapi\external\api
 */
final class api_test extends externallib_advanced_testcase {

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Tests input parameters definition for "get_courses" endpoint.
     */
    public function test_get_courses_parameters(): void {
        $structure = api::get_courses_parameters();
        $this->assertCount(1, $structure->keys);

        $innerstructure = $structure->keys['categoryids'];
        $this->assertTrue( $innerstructure instanceof external_multiple_structure);
        $this->assertEquals('List of category IDs.', $innerstructure->desc);
        $this->assertEquals(VALUE_REQUIRED, $innerstructure->required);

        $componentvalue = $innerstructure->content;
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Category ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);
    }

    /**
     * Tests return value definition for "get_courses" endpoint.
     */
    public function test_get_courses_returns(): void {
        $structure = api::get_courses_returns();
        $this->assertTrue($structure instanceof external_multiple_structure);

        $innerstructure = $structure->content;
        $this->assertTrue($innerstructure instanceof external_single_structure);
        $this->assertCount(3, $innerstructure->keys);

        $componentvalue = $innerstructure->keys['id'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Course ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['name'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Course Name', $componentvalue->desc);
        $this->assertEquals(PARAM_TEXT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['categoryid'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Course Category ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);
    }

    /**
     * Tests "get_courses" endpoint.
     */
    public function test_get_courses(): void {
        // For convenience, let's use a root-level user here.
        // That way, we don't need to deal with user perms for this.
        $this->setAdminUser();

        // Create a course category.
        $coursecategory = $this->getDataGenerator()->create_category();

        // Retrieve courses for this course category.
        $rhett = external_api::clean_returnvalue(
            api::get_courses_returns(),
            api::get_courses([$coursecategory->id])
        );
        $this->assertEmpty($rhett);

        // Create two courses in this category.
        $course1 = $this->getDataGenerator()->create_course(['category' => $coursecategory->id]);
        $course2 = $this->getDataGenerator()->create_course(['category' => $coursecategory->id]);

        // Retrieve course again. There should be two now.
        $rhett = external_api::clean_returnvalue(
            api::get_courses_returns(),
            api::get_courses([$coursecategory->id])
        );
        $this->assertCount(2, $rhett);
        $this->assertEquals($course1->id, $rhett[0]['id']);
        $this->assertEquals($course1->fullname, $rhett[0]['name']);
        $this->assertEquals($coursecategory->id, $rhett[0]['categoryid']);
        $this->assertEquals($course2->id, $rhett[1]['id']);
        $this->assertEquals($course2->fullname, $rhett[1]['name']);
        $this->assertEquals($coursecategory->id, $rhett[1]['categoryid']);
    }

    /**
     * Tests input parameters definition for "get_quizzes" endpoint.
     */
    public function test_get_quizzes_parameters(): void {
        $structure = api::get_quizzes_parameters();
        $this->assertCount(1, $structure->keys);

        $innerstructure = $structure->keys['courseids'];
        $this->assertTrue( $innerstructure instanceof external_multiple_structure);
        $this->assertEquals('List of course IDs.', $innerstructure->desc);
        $this->assertEquals(VALUE_REQUIRED, $innerstructure->required);

        $componentvalue = $innerstructure->content;
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Course ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);
    }

    /**
     * Tests return value definition for "get_quizzes" endpoint.
     */
    public function test_get_quizzes_returns(): void {
        $structure = api::get_quizzes_returns();
        $this->assertTrue($structure instanceof external_multiple_structure);

        $innerstructure = $structure->content;
        $this->assertTrue($innerstructure instanceof external_single_structure);
        $this->assertCount(5, $innerstructure->keys);

        $componentvalue = $innerstructure->keys['id'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Quiz ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['name'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Quiz Name', $componentvalue->desc);
        $this->assertEquals(PARAM_TEXT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['courseid'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Course ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['coursemoduleid'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Course ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['questions'];
        $this->assertTrue($structure instanceof external_multiple_structure);

        $innerstructure2 = $componentvalue->content;
        $this->assertTrue($innerstructure2 instanceof external_single_structure);
        $this->assertCount(2, $innerstructure2->keys);

        $componentvalue = $innerstructure2->keys['id'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Question ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure2->keys['maxmarks'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Maximum marks for this question.', $componentvalue->desc);
        $this->assertEquals(PARAM_FLOAT, $componentvalue->type);
    }

    /**
     * Tests "get_quizzes" endpoint.
     */
    public function test_get_quizzes(): void {
        $this->setAdminUser();

        // Create a course category.
        $coursecategory = $this->getDataGenerator()->create_category();
        // Create two courses in this category.
        $course1 = $this->getDataGenerator()->create_course(['category' => $coursecategory->id]);
        $course2 = $this->getDataGenerator()->create_course(['category' => $coursecategory->id]);

        // Retrieve quizzes for these courses, should come up empty-handed.
        $rhett = external_api::clean_returnvalue(
            api::get_quizzes_returns(),
            api::get_quizzes([$course1->id, $course2->id])
        );
        $this->assertEmpty($rhett);

        // Create three quizzes total in these courses.
        $quiz1 = $this->getDataGenerator()->create_module('quiz', ['course' => $course1->id, 'name' => 'Foo']);
        $quiz2 = $this->getDataGenerator()->create_module('quiz', ['course' => $course1->id, 'name' => 'Bar']);
        $quiz3 = $this->getDataGenerator()->create_module('quiz', ['course' => $course2->id, 'name' => 'Baz']);

        // Add questions to quizzes.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongenerator->create_question_category();
        $maxmark1 = 2.0;
        $maxmark2 = 0.67;
        $question1 = $questiongenerator->create_question('truefalse', null, ['category' => $category->id]);
        quiz_add_quiz_question($question1->id, $quiz1, maxmark: $maxmark1);
        $question2 = $questiongenerator->create_question('truefalse', null, ['category' => $category->id]);
        quiz_add_quiz_question($question2->id, $quiz1, maxmark: $maxmark2);
        $question3 = $questiongenerator->create_question('truefalse', null, ['category' => $category->id]);
        quiz_add_quiz_question($question3->id, $quiz2);

        // Retrieve quizzes again. There should be three now.
        $rhett = external_api::clean_returnvalue(
            api::get_quizzes_returns(),
            api::get_quizzes([$course1->id, $course2->id])
        );

        // Check the output.
        $this->assertCount(3, $rhett);
        $this->assertEquals($quiz1->id, $rhett[0]['id']);
        $this->assertEquals($quiz2->id, $rhett[1]['id']);
        $this->assertEquals($quiz3->id, $rhett[2]['id']);
        $this->assertEquals($course1->id, $rhett[0]['courseid']);
        $this->assertEquals($course1->id, $rhett[1]['courseid']);
        $this->assertEquals($course2->id, $rhett[2]['courseid']);
        $this->assertEquals($quiz1->cmid, $rhett[0]['coursemoduleid']);
        $this->assertEquals($quiz2->cmid, $rhett[1]['coursemoduleid']);
        $this->assertEquals($quiz3->cmid, $rhett[2]['coursemoduleid']);
        $this->assertCount(2, $rhett[0]['questions']);
        $this->assertEquals($question1->id, $rhett[0]['questions'][0]['id']);
        $this->assertEquals($maxmark1, $rhett[0]['questions'][0]['maxmarks']);
        $this->assertEquals($question2->id, $rhett[0]['questions'][1]['id']);
        $this->assertEquals($maxmark2, $rhett[0]['questions'][1]['maxmarks']);
        $this->assertCount(1, $rhett[1]['questions']);
        $this->assertEquals($question3->id, $rhett[1]['questions'][0]['id']);
        $this->assertEquals($question3->defaultmark, $rhett[1]['questions'][0]['maxmarks']);
        $this->assertEmpty($rhett[2]['questions']);
    }

    /**
     * Tests input parameters definition for "get_questions" endpoint.
     */
    public function test_get_questions_parameters(): void {
        $structure = api::get_questions_parameters();
        $this->assertCount(1, $structure->keys);

        $innerstructure = $structure->keys['quizids'];
        $this->assertTrue( $innerstructure instanceof external_multiple_structure);
        $this->assertEquals('List of quiz IDs.', $innerstructure->desc);
        $this->assertEquals(VALUE_REQUIRED, $innerstructure->required);

        $componentvalue = $innerstructure->content;
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Quiz ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);
    }

    /**
     * Tests return value definition for "get_questions" endpoint.
     */
    public function test_get_questions_returns(): void {
        $structure = api::get_questions_returns();
        $this->assertTrue($structure instanceof external_multiple_structure);

        $innerstructure = $structure->content;
        $this->assertTrue($innerstructure instanceof external_single_structure);
        $this->assertCount(8, $innerstructure->keys);

        $componentvalue = $innerstructure->keys['id'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Question ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['name'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Question name', $componentvalue->desc);
        $this->assertEquals(PARAM_TEXT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['text'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Question text', $componentvalue->desc);
        $this->assertEquals(PARAM_RAW, $componentvalue->type);

        $componentvalue = $innerstructure->keys['type'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Question type', $componentvalue->desc);
        $this->assertEquals(PARAM_TEXT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['defaultmarks'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Default marks for this question.', $componentvalue->desc);
        $this->assertEquals(PARAM_FLOAT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['quizzes'];
        $this->assertTrue($componentvalue instanceof external_multiple_structure);

        $innercomponent = $componentvalue->content;
        $this->assertTrue($innercomponent instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $innercomponent->required);
        $this->assertEquals('Quiz ID', $innercomponent->desc);
        $this->assertEquals(PARAM_INT, $innercomponent->type);

        $componentvalue = $innerstructure->keys['revisions'];
        $this->assertTrue($componentvalue instanceof external_multiple_structure);

        $innercomponent = $componentvalue->content;
         $this->assertTrue( $innercomponent instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $innercomponent->required);
        $this->assertEquals('Question ID', $innercomponent->desc);
        $this->assertEquals(PARAM_INT, $innercomponent->type);

        $componentvalue = $innerstructure->keys['questionbankentryid'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('The question bank entry id for this question', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);
    }

    /**
     * Tests "get_questions" endpoint.
     */
    public function test_get_questions(): void {
        $this->setAdminUser();

        // Create a course category.
        $coursecategory = $this->getDataGenerator()->create_category();
        // Create two courses in this category.
        $course1 = $this->getDataGenerator()->create_course(['category' => $coursecategory->id]);
        $course2 = $this->getDataGenerator()->create_course(['category' => $coursecategory->id]);
        // Create three quizzes in these courses.
        $quiz1 = $this->getDataGenerator()->create_module('quiz', ['course' => $course1->id, 'name' => 'Foo']);
        $quiz2 = $this->getDataGenerator()->create_module('quiz', ['course' => $course1->id, 'name' => 'Bar']);
        $quiz3 = $this->getDataGenerator()->create_module('quiz', ['course' => $course2->id, 'name' => 'Baz']);
        // Get a hold of the quiz module contexts.
        list($course, $cm1) = get_course_and_cm_from_instance($quiz1, 'quiz');
        list($course, $cm2) = get_course_and_cm_from_instance($quiz2, 'quiz');
        list($course, $cm3) = get_course_and_cm_from_instance($quiz3, 'quiz');
        $context1 = context_module::instance($cm1->id);
        $context2 = context_module::instance($cm2->id);
        $context3 = context_module::instance($cm2->id);

        // Retrieve questions for these quizzes, should come up empty-handed.
        $rhett = external_api::clean_returnvalue(
            api::get_questions_returns(),
            api::get_questions([$quiz1->id, $quiz2->id, $quiz3->id])
        );
        $this->assertEmpty($rhett);

        // Add questions to quizzes.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongenerator->create_question_category();
        $maxmark1 = 2.0;
        $maxmark2 = 0.67;
        $question1 = $questiongenerator->create_question(
            'truefalse',
            null,
            ['name' => 'Yes or no', 'category' => $category->id]
        );
        // Add this question to quizzes 1 and 2.
        quiz_add_quiz_question($question1->id, $quiz1, maxmark: $maxmark1);
        quiz_add_quiz_question($question1->id, $quiz2, maxmark: $maxmark2);
        $question2 = $questiongenerator->create_question(
            'numerical',
            null,
            ['name' => 'Water boiling temperature', 'category' => $category->id]
        );
        // Add this question to quiz 2.
        quiz_add_quiz_question($question2->id, $quiz2, maxmark: $maxmark2);
        $question3 = $questiongenerator->create_question(
            'multichoice',
            null,
            ['name' => 'Yes, no, or maybe', 'category' => $category->id]
        );
        // Add this question to quizzes 2 and 3.
        quiz_add_quiz_question($question3->id, $quiz2);
        quiz_add_quiz_question($question3->id, $quiz3);

        // Update the third question.
        $question3 = $questiongenerator->update_question($question3, null, ['name' => 'A new name']);

        // Get a handle on all versions of these questions.
        $question1versions = question_bank::get_all_versions_of_question($question1->id);
        $question2versions = question_bank::get_all_versions_of_question($question2->id);
        $question3versions = question_bank::get_all_versions_of_question($question3->id);
        // Re-sort the versions for question 3 to put them into the same order as
        // the revisions are listed in the API output.
        ksort($question3versions);

        // Sanity check - count the number of versions per question.
        // The first two should have one, the third question should have to versions.
        $this->assertCount(1, $question1versions);
        $this->assertCount(1, $question2versions);
        $this->assertCount(2, $question3versions);

        // Retrieve questions again.
        $rhett = external_api::clean_returnvalue(
            api::get_questions_returns(),
            api::get_questions([$quiz1->id, $quiz2->id, $quiz3->id])
        );

        // Compare the output with our generated questions data.
        $this->assertCount(3, $rhett);
        $this->assertEquals($question1->id, $rhett[0]['id']);
        $this->assertEquals(util::format_string($question1->name, $context1), $rhett[0]['name']);
        $this->assertEquals(
            util::format_text($question1->questiontext, $question1->questiontextformat, $context1)[0],
            $rhett[0]['text']
        );
        $this->assertEquals($question1->defaultmark, $rhett[0]['defaultmarks']);
        $this->assertEquals($question1->qtype, $rhett[0]['type']);
        $this->assertEquals($question1->questionbankentryid, $rhett[0]['questionbankentryid']);
        $this->assertEquals([$quiz1->id, $quiz2->id], $rhett[0]['quizzes']);
        $this->assertCount(1, $rhett[0]['revisions']);
        $this->assertEquals(reset($question1versions)->questionid, $rhett[0]['revisions'][0]);

        $this->assertEquals($question2->id, $rhett[1]['id']);
        $this->assertEquals(util::format_string($question2->name, $context2), $rhett[1]['name']);
        $this->assertEquals(
            util::format_text($question2->questiontext, $question2->questiontextformat, $context3)[0],
            $rhett[1]['text']
        );
        $this->assertEquals($question2->defaultmark, $rhett[1]['defaultmarks']);
        $this->assertEquals($question2->qtype, $rhett[1]['type']);
        $this->assertEquals($question2->questionbankentryid, $rhett[1]['questionbankentryid']);
        $this->assertEquals([$quiz2->id], $rhett[1]['quizzes']);
        $this->assertCount(1, $rhett[1]['revisions']);
        $this->assertEquals(reset($question2versions)->questionid, $rhett[1]['revisions'][0]);

        $this->assertEquals($question3->id, $rhett[2]['id']);
        $this->assertEquals(util::format_string($question3->name, $context3), $rhett[2]['name']);
        $this->assertEquals(
            util::format_text($question3->questiontext, $question3->questiontextformat, $context3)[0],
            $rhett[2]['text']
        );
        $this->assertEquals($question3->defaultmark, $rhett[2]['defaultmarks']);
        $this->assertEquals($question3->qtype, $rhett[2]['type']);
        $this->assertEquals($question3->questionbankentryid, $rhett[2]['questionbankentryid']);
        $this->assertEquals([$quiz2->id, $quiz3->id], $rhett[2]['quizzes']);
        $this->assertCount(2, $rhett[2]['revisions']);
        $this->assertEquals(reset($question3versions)->questionid, $rhett[2]['revisions'][0]);
        $this->assertEquals(next($question3versions)->questionid, $rhett[2]['revisions'][1]);
    }

    /**
     * Tests input parameters definition for "get_attempts" endpoint.
     */
    public function test_get_attempts_parameters(): void {
        $structure = api::get_attempts_parameters();
        $this->assertCount(1, $structure->keys);

        $innerstructure = $structure->keys['quizids'];
        $this->assertTrue( $innerstructure instanceof external_multiple_structure);
        $this->assertEquals('List of quiz IDs.', $innerstructure->desc);
        $this->assertEquals(VALUE_REQUIRED, $innerstructure->required);

        $componentvalue = $innerstructure->content;
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Quiz ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);
    }

    /**
     * Tests return value definition for "get_attempts" endpoint.
     */
    public function test_get_attempts_returns(): void {
        $structure = api::get_attempts_returns();
        $this->assertTrue($structure instanceof external_multiple_structure);

        $innerstructure = $structure->content;
        $this->assertTrue($innerstructure instanceof external_single_structure);
        $this->assertCount(6, $innerstructure->keys);

        $componentvalue = $innerstructure->keys['id'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Attempt ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['quizid'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Quiz ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['userid'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('User ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['timestart'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Timestamp of when this attempt was started.', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['timefinish'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Timestamp of when this attempt was finished.', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['questions'];
        $this->assertTrue($componentvalue instanceof external_multiple_structure);

        $innerstructure2 = $componentvalue->content;
        $this->assertTrue($innerstructure2 instanceof external_single_structure);

        $componentvalue = $innerstructure2->keys['id'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Question ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure2->keys['mark'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Mark received', $componentvalue->desc);
        $this->assertEquals(PARAM_FLOAT, $componentvalue->type);

        $componentvalue = $innerstructure2->keys['answer'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('Answer given', $componentvalue->desc);
        $this->assertEquals(PARAM_RAW, $componentvalue->type);
    }

    /**
     * Tests "get_attempts" endpoint.
     */
    public function test_get_attempts(): void {
        $this->markTestIncomplete('To be implemented.');
    }

    /**
     * Tests input parameters definition for "get_users" endpoint.
     */
    public function test_get_users_parameters(): void {
        $structure = api::get_users_parameters();
        $this->assertCount(1, $structure->keys);

        $innerstructure = $structure->keys['userids'];
        $this->assertTrue( $innerstructure instanceof external_multiple_structure);
        $this->assertEquals('List of user IDs.', $innerstructure->desc);
        $this->assertEquals(VALUE_REQUIRED, $innerstructure->required);

        $componentvalue = $innerstructure->content;
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('User ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);
    }

    /**
     * Tests return value definition for "get_users" endpoint.
     */
    public function test_get_users_returns(): void {
        $structure = api::get_users_returns();
        $this->assertTrue($structure instanceof external_multiple_structure);

        $innerstructure = $structure->content;
        $this->assertTrue($innerstructure instanceof external_single_structure);
        $this->assertCount(2, $innerstructure->keys);

        $componentvalue = $innerstructure->keys['id'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('User ID', $componentvalue->desc);
        $this->assertEquals(PARAM_INT, $componentvalue->type);

        $componentvalue = $innerstructure->keys['ucid'];
        $this->assertTrue( $componentvalue instanceof external_value);
        $this->assertEquals(VALUE_REQUIRED, $componentvalue->required);
        $this->assertEquals('UC ID', $componentvalue->desc);
        $this->assertEquals(PARAM_TEXT, $componentvalue->type);
    }

    /**
     * Tests "get_users" endpoint.
     */
    public function test_get_users(): void {
        $this->markTestIncomplete('To be implemented.');
    }
}

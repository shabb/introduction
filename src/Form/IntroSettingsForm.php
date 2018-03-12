<?php

namespace Drupal\intro_guide\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class IntroSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return array('intro_guide.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'intro_guide_settings_form';
  }


  /**
   * Form with 'add more' and 'remove' buttons.
   *
   * This example shows a button to "add more" - add another textfield, and
   * the corresponding "remove" button.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('intro_guide.settings');
    $form['description'] = array(
      '#markup' => '<div>' . t('This example shows an add-more and a remove-last button.') . '</div>',
    );

    $events = $config->get('events');
    $num_events = $form_state->get('num_events');
    // We have to ensure that there is at least one Event field.
    if ($num_events === NULL) {
      $events_field = $form_state->set('num_events', $events);
      $num_events = $events;
    }

    $form['intro_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Intro Guide Settings'),
    );

    // TextField to Capture the node_id.
    $form['intro_settings']['intro_node_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Enter single or multiple paths with comma separated values'),
      '#description' => $this->t('Internal Page Url to show Intro Guide on. You can include other non node path i.e. route name'),
      '#autocomplete_route_name' => 'intro_guide.autocomplete',
      '#autocomplete_route_parameters' => array('field_name' => 'intro_node_url', 'count' => 10),
      '#default_value' => $config->get('intro_node_url'),
    );

    // Title of the Intro Load dialog box.
    $form['intro_settings']['intro_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Intro Load Title'),
      '#description' => t('The text entered here wil be shown as the title of the Intro Load dialog box.'),
      '#default_value' => $config->get('intro_title'),
    );

    // Message shown to the user on the Intro Load dialog box.
    $form['intro_settings']['intro_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Intro Load Message'),
      '#description' => t('The text entered here wil be shown as the message to the user on the Intro Load dialog box.'),
      '#default_value' => $config->get('intro_message'),
    );

    // Radio button to allow user to select the loading method for Intro.Js.
    $form['intro_settings']['intro_load'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Select the method for loading the Intro.Js'),
      '#options' => array(
        'load' => $this->t('onPageLoad'),
        'click' => $this->t('onClick'),
      ),
      '#default_value' => $config->get('intro_load'),
    );

    // Triggering Element.
    $form['intro_settings']['trigger_element'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Triggering Element for IntroJs'),
      '#description' => t('Add the class name (starting with "." for example .classname) or id name (starting with "#" for example #idname) to help Intro.Js identify the element.'),
      '#states' => array(
        'visible' => array(
          ':input[name="intro_load"]' => array('value' => 'click'),
        ),
      ),
      '#default_value' => $config->get('trigger_element'),
    );

    $form['intro_settings']['intro_steps']['#tree'] = TRUE;

    $form['intro_settings']['intro_steps'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Intro Guide Steps'),
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    );

    // For Loop Start here.
    for ($i = 0; $i < $events; $i++) {
      $steps_i = $config->get('steps_' . $i);
      $num_steps_i = $form_state->get('num_steps_' . $i);
      // We have to ensure that there is at least one Event field.
      if ($num_steps_i === NULL) {
        $steps_field_i = $form_state->set('num_steps_' . $i, $steps_i);
        $num_steps_i = $steps_i;
      }

      $form['intro_settings']['intro_steps']['events_' . $i] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Event Name @number', array('@number' => $i + 1)),
        '#description' => $this->t('Add the name of the event.'),
        '#default_value' => $config->get('events_' . $i),
      );
      $form['intro_settings']['intro_steps']['step_' . $i] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('Intro Guide Steps'),
        '#prefix' => '<div id="step-wrapper-' . $i . '">',
        '#suffix' => '</div>',
      );
      for ($j = 0; $j < $num_steps_i; $j++) {
        $form['intro_settings']['intro_steps']['step_' . $i]['events_' . $i . '_steps_' . $j] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Step Number @number', array('@number' => $j + 1)),
          '#description' => $this->t('Add the class name (starting with "." for example .classname) or id name (starting with "#" for example #idname) to which the steps will be applied.'),
          '#default_value' => $config->get('events_' . $i . '_steps_' . $j),
        );
      }
      // Buttons for the Steps.
      $form['intro_settings']['intro_steps']['step_' . $i]['actions'] = array(
        '#type' => 'actions',
      );

      $form['intro_settings']['intro_steps']['step_' . $i]['actions']['add_step'] = array(
        '#type' => 'submit',
        '#value' => t('Add another Step'),
        '#name' => 'step_' . $i,
        '#submit' => ['::addStep'],
        '#ajax' => array(
          'callback' => '::addmoreStepCallback',
          'wrapper' => 'step-wrapper-' . $i,
        ),
      );

      // If there is more than one step, add the remove button.
      if ($num_steps_i > 1) {
        $form['intro_settings']['intro_steps']['step_' . $i]['actions']['remove_step'] = array(
          '#type' => 'submit',
          '#name' => 'step_' . $i,
          '#value' => t('Remove Step'),
          '#submit' => ['::removeStepCallback'],
          '#ajax' => array(
            'callback' => '::addmoreStepCallback',
            'wrapper' => 'step-wrapper-' . $i,
          ),
        );
      }

      $form_state->setCached(FALSE);
    }
    // Buttons for the Event.
    $form['intro_settings']['intro_steps']['actions'] = array(
      '#type' => 'actions',
    );

    $form['intro_settings']['intro_steps']['actions']['add_name'] = array(
      '#type' => 'submit',
      '#value' => t('Add another Event'),
      '#submit' => ['::addOne'],
      '#ajax' => array(
        'callback' => '::addmoreCallback',
        'wrapper' => 'names-fieldset-wrapper',
      ),
    );

    // If there is more than one name, add the remove button.
    if ($num_events > 1) {
      $form['intro_settings']['intro_steps']['actions']['remove_name'] = array(
        '#type' => 'submit',
        '#value' => t('Remove Event'),
        '#submit' => ['::removeCallback'],
        '#ajax' => array(
          'callback' => '::addmoreCallback',
          'wrapper' => 'names-fieldset-wrapper',
        ),
      );
    }

    $form_state->setCached(FALSE);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['intro_settings']['intro_steps'];
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('intro_guide.settings');
    $events = $config->get('events');
    $add_button = $events + 1;
    $form_state->set('num_events', $add_button);
    $config->set('events', $form_state->get('num_events'))->save();
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('intro_guide.settings');
    $events_field = $form_state->get('num_events');
    if ($events_field > 1) {
      $remove_button = $events_field - 1;
      $form_state->set('num_events', $remove_button);
      $config->set('events', $form_state->get('num_events'))->save();
      for ($i = $remove_button; $i <= $remove_button; $i++) {
        $config->set('events_' . $i, '')->save();
        for ($j = 0; $j < $config->get('steps_' . $i); $j++) {
          $config->set('events_' . $i . '_steps_' . $j, '')->save();
        }
      }
      $form_state->set('num_steps_' . $remove_button, 0);
      $config->set('steps_' . $remove_button, 0)->save();
    }
    $form_state->setRebuild();
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreStepCallback(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('intro_guide.settings');
    $steps = $config->get('steps');
    $button_event = $form_state->getTriggeringElement()['#name'];
    $i = filter_var($button_event, FILTER_SANITIZE_NUMBER_INT);
    return $form['intro_settings']['intro_steps']['step_' . $i];
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addStep(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('intro_guide.settings');
    $events = $config->get('events');
    $button_event = $form_state->getTriggeringElement()['#name'];
    $i = filter_var($button_event, FILTER_SANITIZE_NUMBER_INT);
    $steps_i = $config->get('steps_' . $i);

    $add_button = $steps_i + 1;
    $num_events = $form_state->set('num_steps_' . $i, $add_button);
    $config->set('steps_' . $i, $form_state->get('num_steps_' . $i))->save();
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeStepCallback(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('intro_guide.settings');
    $button_event = $form_state->getTriggeringElement()['#name'];
    $i = filter_var($button_event, FILTER_SANITIZE_NUMBER_INT);
    $steps_field_i = $form_state->get('num_steps_' . $i);
    if ($steps_field_i > 1) {
      $remove_button = $steps_field_i - 1;
      $form_state->set('num_steps_' . $i, $remove_button);
      $config->set('steps_' . $i, $form_state->get('num_steps_' . $i))->save();
      for ($j = $remove_button; $j <= $remove_button; $j++) {
        $config->set('events_' . $i . '_steps_' . $j, '')->save();
      }
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('intro_guide.settings');

    if ($form_state->getValue('intro_load') == 'click') {
      if ($form_state->getValue('trigger_element') == '') {
        $form_state->setErrorByName('trigger_element', $this->t('Triggerring Element cannot be empty.'));
      }
      if (substr($form_state->getValue('trigger_element'), 0, 1) !== '.' && substr($form_state->getValue('trigger_element'), 0, 1) !== '#') {
        $form_state->setErrorByName('trigger_element', $this->t('Triggerring Element should either start with a dot or a hash sign.'));
      }
    }

    $num_events = $form_state->get('num_events');
    $config->set('events', $num_events)->save();
    $events = $config->get('events');
    for ($i = 0; $i < $events; $i++) {
      $num_steps_i = $form_state->get('num_steps_' . $i);
      for ($j = 0; $j < $num_steps_i; $j++) {
        if (substr($form_state->getValue('events_' . $i . '_steps_' . $j), 0, 1) !== '.' && substr($form_state->getValue('events_' . $i . '_steps_' . $j), 0, 1) !== '#') {
          $form_state->setErrorByName('events_' . $i . '_steps_' . $j, $this->t('Step should either start with a dot or a hash sign.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('intro_guide.settings');
    // kint($config);exit;
    $page_url = $form_state->getValue('intro_node_url');
    $paths = explode(', ', $page_url);
    foreach ($paths as $path) {
      // Check if the node exists.
      if (is_numeric($path)) {
        $is_valid = \Drupal::service('path.validator')->isValid('node/' . $path);
      }
      // Check if the path entered is in the available routes.
      else {
        $is_valid = \Drupal::service('path.validator')->isValid($path);
      }
      if ($is_valid && $page_url !== '') {
        $config->set('intro_node_url', $page_url)->save();
      }
      else {
        drupal_set_message($this->t('"<i>@path</i>" is not a valid path or route. Please enter a valid internal path or route name.', array('@path' => $path)), 'error');
      }
    }

    if ($form_state->getValue('intro_title') !== '') {
      $config->set('intro_title', $form_state->getValue('intro_title'))->save();
    }

    if ($form_state->getValue('intro_message') !== '') {
      $config->set('intro_message', $form_state->getValue('intro_message'))->save();
    }

    if ($form_state->getValue('intro_load') !== '') {
      $config->set('intro_load', $form_state->getValue('intro_load'))->save();
      if ($config->get('intro_load') == 'click') {
        $config->set('trigger_element', $form_state->getValue('trigger_element'))->save();
      }
      else {
        $config->set('trigger_element', '')->save();
      }
    }

    $num_events = $form_state->get('num_events');
    $config->set('events', $num_events)->save();

    $events = $config->get('events');
    for ($i = 0; $i < $events; $i++) {
      $num_steps_i = $form_state->get('num_steps_' . $i);
      $config->set('steps_' . $i, $num_steps_i)->save();
      $config->set('events_' . $i, $form_state->getValue('events_' . $i))->save();
      for ($j = 0; $j < $num_steps_i; $j++) {
        $config->set('events_' . $i . '_steps_' . $j, $form_state->getValue('events_' . $i . '_steps_' . $j))->save();
      }
    }
  }
}
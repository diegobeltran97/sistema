<?php

/* login.twig */
class __TwigTemplate_8e4a1c60fd93d00f797b9b53617c3d304e3bf060729bdaf606bba4469ee551ee extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        $this->env->loadTemplate("header.twig")->display($context);
        // line 2
        echo "111

";
        // line 4
        $this->env->loadTemplate("footer.twig")->display($context);
    }

    public function getTemplateName()
    {
        return "login.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  25 => 4,  21 => 2,  19 => 1,);
    }
}

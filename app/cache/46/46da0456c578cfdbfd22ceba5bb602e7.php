<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* home.twig */
class __TwigTemplate_5feb4f638f2ab35568a6f427bf98fa7f extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>ArticleHub - Liste des Articles</title>
    <script src=\"https://cdn.tailwindcss.com\"></script>
</head>
<body class=\"bg-gray-100 min-h-screen\">
    <header class=\"bg-indigo-600 text-white p-4 shadow-md\">
        <div class=\"container mx-auto flex justify-between items-center\">
            <h1 class=\"text-2xl font-bold\">ArticleHub</h1>
            <button id=\"addArticleBtn\" class=\"bg-white text-indigo-600 px-4 py-2 rounded-full hover:bg-indigo-100 transition duration-300\">
                Ajouter un Article
            </button>
        </div>
    </header>

    <main class=\"container mx-auto mt-8 p-4\">
        <div id=\"articlesList\" class=\"grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6\">
        ";
        // line 21
        if (($context["articles"] ?? null)) {
            // line 22
            yield "                ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["articles"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["article"]) {
                // line 23
                yield "                    <article class=\"bg-white rounded-lg shadow-md overflow-hidden\">
                        <div class=\"p-6\">
                            <h2 class=\"text-xl font-semibold mb-2\">";
                // line 25
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["article"], "title", [], "any", false, false, false, 25), "html", null, true);
                yield "</h2>
                            <p class=\"text-gray-600\">";
                // line 26
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["article"], "content", [], "any", false, false, false, 26), "html", null, true);
                yield "</p>
                        </div>
                    </article>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['article'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 30
            yield "            ";
        } else {
            // line 31
            yield "                <p>No articles available.</p>
            ";
        }
        // line 33
        yield "        </div>
    </main>

    <!-- Modal pour ajouter un article -->
    <div id=\"addArticleModal\" class=\"fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center\">
        <div class=\"bg-white p-8 rounded-lg shadow-xl w-full max-w-md\">
            <h2 class=\"text-2xl font-bold mb-4\">Ajouter un Nouvel Article</h2>
            <form id=\"addArticleForm\" action=\"/createArticle\" method=\"POST\" class=\"space-y-4\">
                <div>
                    <label for=\"articleTitle\" class=\"block text-sm font-medium text-gray-700\">Titre</label>
                    <input type=\"text\" id=\"articleTitle\" name=\"title\" required class=\"mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\">
                </div>
                <div>
                    <label for=\"articleContent\" class=\"block text-sm font-medium text-gray-700\">Contenu</label>
                    <textarea id=\"articleContent\" name=\"content\" rows=\"4\" required class=\"mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500\"></textarea>
                </div>
                <div class=\"flex justify-end space-x-3\">
                    <button type=\"button\" id=\"cancelBtn\" class=\"px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300\">Annuler</button>
                    <button type=\"submit\" class=\"px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700\">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const addArticleBtn = document.getElementById('addArticleBtn');
        const addArticleModal = document.getElementById('addArticleModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const addArticleForm = document.getElementById('addArticleForm');

        addArticleBtn.addEventListener('click', () => {
            addArticleModal.classList.remove('hidden');
            addArticleModal.classList.add('flex');
        });

        cancelBtn.addEventListener('click', () => {
            addArticleModal.classList.add('hidden');
            addArticleModal.classList.remove('flex');
        });
    </script>
</body>
</html>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "home.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  96 => 33,  92 => 31,  89 => 30,  79 => 26,  75 => 25,  71 => 23,  66 => 22,  64 => 21,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "home.twig", "C:\\laragon\\www\\ABDELMOUHAIMINE-ELJASSIMI-PROJET-MVC-PHP\\app\\views\\front\\home.twig");
    }
}

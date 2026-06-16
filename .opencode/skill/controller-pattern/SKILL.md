---
name: controller-pattern
description: Canonical 5-layer architecture for any Symfony controller in smestaj (create OR modify). Use whenever the task touches src/AdminBundle/Controller/, src/SiteBundle/Controller/, or src/LogBundle/Controller/ — including new routes, new actions, refactoring existing endpoints, adding API endpoints, wiring page controllers, or adding/changing DTOs, parsers, validators, or views that back a controller. Enforces DTO + Parser + entity-level Validator + View + thin controller separation, with Symfony's MapRequestPayload / MapQueryString hydration. Triggers on phrases like "create controller", "new controller", "add controller", "scaffold controller", "edit controller", "add route", "new endpoint", "API endpoint", "controller action".
---

# Controller Pattern — smestaj

Every controller in smestaj follows the same 5-layer separation, no exceptions:

```
DTO  →  Parser  →  Entity (Assert-annotated)  →  Validator  →  Handler  →  View
```

Announce on load: `[SKILL] controller-pattern loaded`.

---

## 0. Hard rules

1. Controller actions NEVER receive `Symfony\Component\HttpFoundation\Request` as the input payload. They receive a typed DTO, hydrated by `#[MapRequestPayload]` / `#[MapQueryString]`. **This includes file uploads** — `UploadedFile` properties live on the DTO and are bound automatically by `#[MapRequestPayload(acceptFormat: 'form')]` for multipart bodies. The only remaining legitimate `Request` usage in a controller is CSRF token retrieval for state-changing routes.
2. DTO → Entity translation lives in a dedicated Parser. The controller does NOT touch entity setters.
3. Entity-level validation lives on the entity (Symfony `Assert\` constraints), and is **triggered by a dedicated Validator class** that the controller calls explicitly after the parser and before the handler. DTO-level validation runs automatically inside `MapRequestPayload` — that catches shape errors at the boundary. Entity-level validation catches domain invariants (uniqueness, cross-field rules, publish gates).
4. Every response — JSON or Twig array — goes through a View class. Controllers NEVER hand-build `['id' => …, 'title' => …]` payloads.
5. Controllers are thin: parse → validate → handle → view → return. If the action body grows beyond ~15 lines of glue, you have logic that belongs in a layer below.

If any layer is missing for the bundle/feature you are working on, you create it. No exceptions.

---

## 1. Layer 1 — DTO (`src/<Bundle>/Dto/`)

### Where

| Bundle | Path |
|---|---|
| AdminBundle | `src/AdminBundle/Dto/<Feature>/<Action>Dto.php` |
| SiteBundle | `src/SiteBundle/Dto/<Feature>/<Action>Dto.php` (already exists as a folder) |
| LogBundle | `src/LogBundle/Dto/<Feature>/<Action>Dto.php` |

If the bundle's `Dto/` folder doesn't exist yet, create it. AdminBundle does not have one yet — create `src/AdminBundle/Dto/` the first time you need it.

### Shape

- `final` class.
- Typed `public` properties with sensible defaults (`= ''`, `= 0`, `= []`, `= null`). No getters/setters — DTOs are dumb data carriers.
- `Symfony\Component\Validator\Constraints as Assert` attributes on every property that has shape requirements (`NotBlank`, `Length`, `Email`, `Type`, `Range`, `Positive`, `Choice`, …).
- No constructor for DTOs hydrated by `MapRequestPayload` (Symfony instantiates via reflection and sets properties).
- One DTO per controller action. Do not share `ProductDto` between Create and Update — they have different required fields. Make `ProductCreateDto` and `ProductUpdateDto`.

### Nested DTOs

When the payload contains a nested structure (sub-resource, collection of items, related entity data), make a nested DTO in the same folder.

- Type the parent's property as `<NestedDto>` or `array<int, <NestedDto>>` (use the PHPDoc array shape annotation).
- Put `#[Assert\Valid]` on the parent property so Symfony recurses validation into the nested DTO.
- Each nested DTO gets its own Parser (Layer 3).

### File uploads in DTOs

`UploadedFile` is a first-class DTO property. **Do NOT touch `$request->files` from the controller.** Symfony's `MapRequestPayload(acceptFormat: 'form')` binds multipart files directly into DTO properties.

| Form field shape | DTO property type | Validation constraint |
|---|---|---|
| Single file (`<input type="file" name="hostPhoto">`) | `public ?UploadedFile $hostPhoto = null;` | `#[Assert\Image]` or `#[Assert\File(maxSize: '5M', mimeTypes: [...])]` |
| Multiple files, indexed array (`<input type="file" name="images[]" multiple>`) | `public array $images = [];` + PHPDoc `@var array<int, UploadedFile>` | `#[Assert\All([new Assert\Image(maxSize: '5M')])]` and `#[Assert\Count(max: 20)]` |
| Multiple files, named keys (`<input type="file" name="images[main]">`, `<input type="file" name="images[side]">`) | `public array $images = [];` + PHPDoc `@var array<string, UploadedFile>` | `#[Assert\All([new Assert\Image(...)])]` |

Rules:

- Always type `UploadedFile` (or `?UploadedFile` for optional single, `array` for collections — typed via PHPDoc since PHP has no generics).
- Always attach `Assert\Image` / `Assert\File` constraints with sensible `maxSize`, `mimeTypes`, and (for images) `maxWidth`/`maxHeight` limits.
- For collections, combine `Assert\All([new Assert\Image(...)])` (per-item rules) with `Assert\Count(max: …)` (collection-size rule).
- `Assert\NotNull` on a required file; omit it for optional uploads.
- The controller signature must use `#[MapRequestPayload(acceptFormat: 'form')]` (not the default JSON mode) — multipart requires the form denormaliser.
- The Parser (Layer 3) receives the `UploadedFile`(s) as part of the DTO it already accepts; it does NOT take a separate `$images` argument.

### Example — root DTO

```php
<?php

declare(strict_types=1);

namespace AdminBundle\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

final class ProductCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 200)]
    public string $title = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $code = '';

    #[Assert\Positive]
    public int $categoryId = 0;

    /**
     * @var array<int, ProductOptionDto>
     */
    #[Assert\Valid]
    public array $options = [];
}
```

### Example — nested DTO

```php
<?php

declare(strict_types=1);

namespace AdminBundle\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

final class ProductOptionDto
{
    #[Assert\NotBlank]
    public string $countryCode = '';

    #[Assert\Type('numeric')]
    #[Assert\PositiveOrZero]
    public float $price = 0.0;
}
```

### Example — DTO with file uploads

```php
<?php

declare(strict_types=1);

namespace AdminBundle\Dto\InfoPage;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class InfoPageUpdateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    public string $propertyName = '';

    #[Assert\Image(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    public ?UploadedFile $hostPhoto = null;

    /**
     * @var array<int, UploadedFile>
     */
    #[Assert\Count(max: 20)]
    #[Assert\All([
        new Assert\Image(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp']),
    ])]
    public array $imageUploads = [];

    /**
     * @var array<int, int>
     */
    public array $imageDeleteIds = [];
}
```

---

## 2. Layer 2 — Controller binding (the attribute table)

### JSON body (POST/PUT/PATCH with `Content-Type: application/json`)

```php
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/api/products', name: 'admin.api.products.create', methods: ['POST'], options: ['expose' => true])]
public function create(#[MapRequestPayload] ProductCreateDto $dto): JsonResponse
```

### Form body (`application/x-www-form-urlencoded` or `multipart/form-data` — no files)

```php
#[Route('/api/products', methods: ['POST'], options: ['expose' => true])]
public function create(#[MapRequestPayload(acceptFormat: 'form')] ProductCreateDto $dto): JsonResponse
```

### Query string (GET with filters/pagination)

```php
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[Route('/api/products', methods: ['GET'], options: ['expose' => true])]
public function list(#[MapQueryString] ProductListDto $dto = new ProductListDto()): JsonResponse
```

The `= new ProductListDto()` default tolerates empty query strings.

### Path-only routes (`/api/products/{id}` show/delete)

`MapRequestPayload` does not bind path params. Construct the DTO manually with the path value, but keep the rule: the entity is built from a DTO, not from a raw int.

```php
#[Route('/api/products/{id}', methods: ['GET'], requirements: ['id' => '\d+'], options: ['expose' => true])]
public function show(int $id): JsonResponse
{
    $dto = new ProductShowDto();
    $dto->id = $id;
    // … repository lookup, view
}
```

### Multipart with file uploads

Files belong on the DTO. Use `#[MapRequestPayload(acceptFormat: 'form')]` — Symfony binds the multipart body's scalar fields AND `UploadedFile` properties into the DTO in one shot.

```php
#[Route('/api/info-pages/{id}', methods: ['POST'], requirements: ['id' => '\d+'], options: ['expose' => true])]
public function update(
    int $id,
    #[MapRequestPayload(acceptFormat: 'form')] InfoPageUpdateDto $dto,
): JsonResponse {
    $entity = $this->infoPageRepository->find($id);

    if (false === $entity instanceof AdsInfoPage) {
        throw new NotFoundHttpException();
    }

    $entity = $this->parser->parse($dto, $entity);

    $violations = $this->validator->validate($entity);

    if (0 < count($violations)) {
        return new JsonResponse(['ok' => false, 'violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $this->handler->save($entity);

    return new JsonResponse($this->view->view($entity));
}
```

Note: there is no `Request $request` parameter. There is no `$request->files->get(...)` call. The `UploadedFile`s arrive on the DTO, already validated against the `Assert\Image` / `Assert\File` constraints declared on those DTO properties. The Parser consumes them as it does any other DTO property.

### What Symfony auto-handles for you

- Hydrates DTO public properties from the body/query.
- Runs `ValidatorInterface->validate($dto)` automatically using the Assert constraints you declared.
- On a structural validation failure, throws `HttpException` with status `422 Unprocessable Entity` carrying a JSON body describing the violations. **Do not catch this.** Let it propagate — the framework returns the right response.

DTO-level validation is the boundary check. Layer 4 (entity validator) is the domain check.

---

## 3. Layer 3 — Parser (`src/<Bundle>/Parser/`)

### Where

| Bundle | Path |
|---|---|
| AdminBundle | `src/AdminBundle/Parser/<Feature>/<Action>Parser.php` |
| SiteBundle | `src/SiteBundle/Parser/<Feature>/<Action>Parser.php` |

Group parsers under a `<Feature>/` subfolder when the bundle accumulates more than ~3 parsers per feature (mirrors the `AdminBundle/Parser/Admin/UserEditRequestParser.php` precedent).

### Shape

- `final` class.
- Constructor DI for collaborators (repositories, slugger, password hasher, child parsers, `TextHelper`, etc.).
- Single public method: `parse(<Dto> $dto, ?<Entity> $entity = null): <Entity>` — when `$entity` is null, instantiate a new one; otherwise mutate in place.
- No HTTP types (`Request`, `ParameterBag`, `Session` — forbidden).
- No persistence calls (`EntityManagerInterface::persist/flush` — forbidden; handler owns that).
- No validator calls (Layer 4 owns that).
- No `dd()`, no `error_log()`, no logging — pure data translation.

### Nested DTO → nested Parser

One parser per DTO type. The outer parser composes the inner via constructor DI and iterates the nested DTO collection.

### Example

```php
<?php

declare(strict_types=1);

namespace AdminBundle\Parser\Product;

use AdminBundle\Dto\Product\ProductCreateDto;
use SiteBundle\Entity\Ads;
use SiteBundle\Helper\TextHelper;
use SiteBundle\Repository\CategoryRepository;

final class ProductCreateParser
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly TextHelper $textHelper,
        private readonly ProductOptionParser $optionParser,
    ) {
    }

    public function parse(ProductCreateDto $dto, ?Ads $entity = null): Ads
    {
        if (null === $entity) {
            $entity = new Ads();
        }

        $entity->setTitle($this->textHelper->clearText($dto->title));
        $entity->setCode($dto->code);
        $entity->setCategoryId($this->categoryRepository->find($dto->categoryId));

        foreach ($dto->options as $optionDto) {
            $entity->addOption($this->optionParser->parse($optionDto));
        }

        return $entity;
    }
}
```

---

## 4. Layer 4 — Entity-level Validator (`src/<Bundle>/Validator/`)

### Where

| Bundle | Path |
|---|---|
| AdminBundle | `src/AdminBundle/Validator/<Entity>Validator.php` (folder does not exist — create it) |
| SiteBundle | `src/SiteBundle/Validator/<Entity>Validator.php` (folder does not exist — create it) |

**Do NOT use `src/SiteBundle/Validators/` (plural-s).** That is the legacy `ValidatorContainer` array-based system from 2017. The canonical pattern uses `Validator/` (singular) and Symfony's `ValidatorInterface`.

### Entity constraints

Add `Symfony\Component\Validator\Constraints as Assert` attributes directly on the entity property declarations. Use `groups: ['<group>']` when constraints only apply in some contexts (e.g. stricter checks before publishing). Reference: `src/SiteBundle/Entity/AdsInfoPage.php`.

```php
#[ORM\Column(type: 'string', length: 200)]
#[Assert\NotBlank(groups: ['Default', 'publish'])]
#[Assert\Length(max: 200, groups: ['Default', 'publish'])]
private string $title = '';
```

### Validator class shape

```php
<?php

declare(strict_types=1);

namespace AdminBundle\Validator;

use SiteBundle\Entity\Ads;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @param array<int, string>|null $groups
     *
     * @return array<string, string> field path => first violation message
     */
    public function validate(Ads $entity, ?array $groups = null): array
    {
        $violations = $this->validator->validate($entity, null, $groups);

        $result = [];

        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();

            if (true === array_key_exists($path, $result)) {
                continue;
            }

            $result[$path] = (string) $violation->getMessage();
        }

        return $result;
    }
}
```

Reference implementation already in the codebase: `src/SiteBundle/Services/InfoPage/AdsInfoPagePublishValidator.php`.

### Controller usage — API (JSON)

```php
$entity = $this->parser->parse($dto);

$violations = $this->validator->validate($entity);

if (0 < count($violations)) {
    return new JsonResponse([
        'ok' => false,
        'violations' => $violations,
    ], Response::HTTP_UNPROCESSABLE_ENTITY);
}

$this->handler->save($entity);

return new JsonResponse($this->view->view($entity), Response::HTTP_CREATED);
```

### Controller usage — page (Twig)

```php
$entity = $this->parser->parse($dto, $existing);

$violations = $this->validator->validate($entity);

if (0 < count($violations)) {
    foreach ($violations as $field => $message) {
        $this->addFlash('error', sprintf('%s: %s', $field, $message));
    }

    return $this->render(
        '@Admin/Pages/productEdit.html.twig',
        $this->responseFormatter->formatResponse($entity, $violations),
    );
}

$this->handler->save($entity);

return $this->redirectToRoute('admin.product.edit', ['id' => $entity->getId()]);
```

---

## 5. Layer 5 — View (`src/<Bundle>/View/`)

### Where

| Bundle | Path |
|---|---|
| AdminBundle | `src/AdminBundle/View/<Entity>View.php` (folder does not exist — create it) |
| SiteBundle | `src/SiteBundle/View/<Entity>View.php` (exists) |

### Shared interface — `SiteBundle\View\ViewInterface`

Lives in SiteBundle because `EntityInterface` lives there and is shared across bundles. If `src/SiteBundle/View/ViewInterface.php` does not exist, create it:

```php
<?php

declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\EntityInterface;

interface ViewInterface
{
    /**
     * @return array<string, mixed>
     */
    public function view(EntityInterface $entity): array;
}
```

Every view class in every bundle implements this interface.

### View class shape

- `final` class.
- `implements ViewInterface`.
- `view(EntityInterface $entity): array` — verify the concrete type with `instanceof` and throw `InvalidArgumentException` on mismatch (PHP's contravariance forces the parameter to remain `EntityInterface`).
- Returns **ALL** persisted properties of the entity: id, scalars, datetimes as ISO strings, FK ids/aliases, status, timestamps, etc. If the entity has it, the view exposes it.
- Nested entities → delegate to their own `<NestedEntity>View` (constructor-injected). Never inline `['id' => $nested->getId(), …]`.
- Constructor DI for collaborators (router, translator, child views, purifier, helpers).
- Additional public or private helper methods are permitted (`socialMetaDataContent`, `getLdView`, `getImages`, `getMainImage`, etc. — see `src/SiteBundle/View/AdView.php`). The mandatory contract is the `view()` method; helpers are bonus.
- Date formatting, slugification, URL generation, HTML purification — all live here, NOT in the controller and NOT in the entity.

### Reusing SiteBundle views

Shared domain entities (`Category`, `Media`, `Tag`, `Contact`, `Adshastags`, etc.) already have views in `src/SiteBundle/View/`. **Reuse them.** Do not create `AdminBundle\View\CategoryView` if `SiteBundle\View\CategoryView` already exists.

### Example

```php
<?php

declare(strict_types=1);

namespace AdminBundle\View;

use InvalidArgumentException;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\View\CategoryView;
use SiteBundle\View\ImageView;
use SiteBundle\View\ViewInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductView implements ViewInterface
{
    public function __construct(
        private readonly CategoryView $categoryView,
        private readonly ImageView $imageView,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function view(EntityInterface $entity): array
    {
        if (false === $entity instanceof Ads) {
            throw new InvalidArgumentException(sprintf('Expected %s, got %s', Ads::class, $entity::class));
        }

        return [
            'id' => $entity->getId(),
            'title' => $entity->getTitle(),
            'code' => $entity->getCode(),
            'status' => $entity->getStatus(),
            'category' => $this->categoryView->view($entity->getCategoryId()),
            'images' => $this->buildImages($entity),
            'created_at' => $entity->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updated_at' => $entity->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    private function buildImages(Ads $entity): array
    {
        $images = [];

        foreach ($entity->getMedia() as $media) {
            $images[] = $this->imageView->view($media, $this->translator->trans('ads'), ['single', 'single_thumb']);
        }

        return $images;
    }
}
```

### Controller usage — JSON

```php
return new JsonResponse($this->view->view($entity), Response::HTTP_CREATED);
```

### Controller usage — Twig

```php
return $this->render('@Admin/Pages/productEdit.html.twig', [
    'product' => $this->view->view($entity),
    'languages' => $this->languages,        // non-entity context
    'csrfToken' => $this->getCsrfToken(),   // non-entity context
]);
```

For composite Twig pages backed by multiple entities, keep the existing `*ResponseFormatter` pattern (`src/AdminBundle/Formatter/InfoPageEditResponseFormatter.php`) — but the Formatter's job is to **compose** View outputs and add page-level context (form options, language lists, URLs). Each entity field in the final array must still flow through its View, not be hand-built.

---

## 6. Controller skeleton — copy-paste reference

```php
<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Product\Api;

use AdminBundle\Dto\Product\ProductCreateDto;
use AdminBundle\Handler\ProductEditHandler;
use AdminBundle\Parser\Product\ProductCreateParser;
use AdminBundle\Validator\ProductValidator;
use AdminBundle\View\ProductView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ProductCreateController extends AbstractController
{
    public function __construct(
        private readonly ProductCreateParser $parser,
        private readonly ProductValidator $validator,
        private readonly ProductEditHandler $handler,
        private readonly ProductView $view,
    ) {
    }

    #[Route('/api/products', name: 'admin.api.products.create', methods: ['POST'], options: ['expose' => true])]
    public function create(
        Request $request,
        #[MapRequestPayload] ProductCreateDto $dto,
    ): JsonResponse {
        if (false === $this->isCsrfTokenValid('product_create', (string) $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException();
        }

        $entity = $this->parser->parse($dto);

        $violations = $this->validator->validate($entity);

        if (0 < count($violations)) {
            return new JsonResponse([
                'ok' => false,
                'violations' => $violations,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->handler->save($entity);

        return new JsonResponse($this->view->view($entity), Response::HTTP_CREATED);
    }
}
```

Note: the existing codebase has a recurring bug where `$this->createAccessDeniedException()` is called without `throw` (see `ProductEditController.php`, `UserEditController.php`). The expression is a no-op without `throw`. **Always `throw $this->createAccessDeniedException();`** — do not copy the bug.

---

## 7. Pre-completion checklist

Before ticking the IMPLEMENTATION_PLAN.md step, every item below must be true:

- [ ] DTO exists in `src/<Bundle>/Dto/<Feature>/` (folder created if missing).
- [ ] DTO is `final`, has typed public properties, and `Assert\` constraints on every shape-significant field.
- [ ] File uploads are declared as `UploadedFile` / `?UploadedFile` / `array<int, UploadedFile>` properties ON THE DTO, with `Assert\Image` or `Assert\File` constraints (size, mime-types, count). No `$request->files` in the controller.
- [ ] Each nested structure has its own DTO; parent property carries `#[Assert\Valid]`.
- [ ] Controller signature uses `#[MapRequestPayload]` (JSON body), `#[MapRequestPayload(acceptFormat: 'form')]` (form or multipart, including files), `#[MapQueryString]` (GET query), or manual DTO construction (path-only). The only legitimate `Request` argument is for CSRF token retrieval.
- [ ] Parser exists in `src/<Bundle>/Parser/<Feature>/`, signature `parse(<Dto> $dto, ?<Entity> $entity = null): <Entity>`.
- [ ] Each nested DTO has a matching parser, constructor-injected into the outer parser.
- [ ] Entity carries `Assert\` constraints on persisted properties (with `groups` where appropriate).
- [ ] Validator exists in `src/<Bundle>/Validator/` (singular!), wraps `ValidatorInterface`, returns `array<string, string>`.
- [ ] Controller calls validator AFTER parser and BEFORE handler; short-circuits with 422 (API) or flash + re-render (page).
- [ ] View exists in `src/<Bundle>/View/`, implements `SiteBundle\View\ViewInterface`, returns ALL entity properties.
- [ ] Nested entities in the view delegate to their own View class.
- [ ] CSRF token check uses `throw $this->createAccessDeniedException();` (with `throw`!).
- [ ] Controller is thin (≤~15 lines of glue per action). No entity hand-building. No response-array hand-building.
- [ ] If a route was added/changed: ran `docker exec smestaj-app php bin/console fos:js-routing:dump` (per AGENTS.md).

---

## 8. Anti-patterns — do NOT do

- Controller signature `public function create(Request $request)` for a body or query route. Request is forbidden as the data input — DTO only.
- `$entity->setX($request->request->get('x'))` in the controller. That belongs in the Parser.
- `$request->files->get('photo')` or `$request->files->all('images')` anywhere in a controller. Files belong on the DTO as `UploadedFile` / `array<int, UploadedFile>` properties bound by `#[MapRequestPayload(acceptFormat: 'form')]`.
- Passing files as a separate Parser argument (`parser->parse($dto, $files, $entity)`). The Parser receives the DTO only — files live on the DTO.
- Skipping `Assert\Image` / `Assert\File` on UploadedFile properties. Every file property MUST declare size, mime-type, and (for collections) count constraints — otherwise you have an upload-anything endpoint.
- `return $this->json(['id' => $entity->getId(), 'title' => $entity->getTitle()])` — bypasses the View. Use `return new JsonResponse($this->view->view($entity))`.
- `if ('' === $dto->title) { return new JsonResponse(['error' => …], 422); }` in the controller. That is what `#[Assert\NotBlank]` on the DTO is for; Symfony will return 422 for you.
- `try/catch (ValidationFailedException)` to convert MapRequestPayload's automatic exception into a custom shape — let the framework handle it. Custom shaping is only for the entity-level validator's output.
- Skipping the View "because it is just an id" — every JSON or Twig array goes through a View.
- Hand-rolling `if` validation inside the Validator class — declare the rules with `Assert\` on the entity and delegate to `ValidatorInterface`.
- Creating a file under `src/SiteBundle/Validators/` (plural). That folder is legacy. Use `Validator/` (singular).
- Sharing one DTO across Create and Update when the required fields differ — make `XCreateDto` and `XUpdateDto`.
- Duplicating an existing View. If `SiteBundle\View\CategoryView` exists, AdminBundle reuses it.

---

## 9. When to load this skill

Load with `skill({ name: "controller-pattern" })` **before** writing or editing ANY file under:

- `src/AdminBundle/Controller/`
- `src/SiteBundle/Controller/`
- `src/LogBundle/Controller/`

…or before creating/editing any file in:

- `src/<Bundle>/Dto/`
- `src/<Bundle>/Parser/`
- `src/<Bundle>/Validator/`
- `src/<Bundle>/View/`

If you started a controller change without loading the skill, stop, load it now, and bring the already-written code into compliance before continuing.

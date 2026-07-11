---
name: controller-pattern
description: Canonical 5-layer architecture for any Symfony controller in smestaj (create OR modify). Use whenever the task touches src/AdminBundle/Controller/, src/SiteBundle/Controller/, or src/LogBundle/Controller/ — including new routes, new actions, refactoring existing endpoints, adding API endpoints, wiring page controllers, or adding/changing DTOs, parsers, validators, or views that back a controller. Enforces DTO + Parser + entity-level Validator + View + thin controller separation, with Symfony's MapRequestPayload / MapQueryString hydration. Triggers on phrases like "create controller", "new controller", "add controller", "scaffold controller", "edit controller", "add route", "new endpoint", "API endpoint", "controller action".
---

# Controller Pattern — smestaj

Every controller in smestaj follows the same 5-layer separation:

```
DTO  →  Controller (payload mapping + CSRF)  →  Parser (DTO→Entity)  →  Handler (persist)  →  View (JSON/Twig)
```

Announce on load: `[SKILL] controller-pattern loaded`.

Symfony reference for the payload-mapping attributes: <https://symfony.com/doc/current/controller.html#automatic-mapping-of-the-request>.

---

## 1. Purpose & scope

This skill governs every controller in:

- `src/SiteBundle/Controller/` (public site + JSON API under `Api/`).
- `src/AdminBundle/Controller/` (admin dashboard + `*/Api/` JSON endpoints).
- `src/LogBundle/Controller/` (forward-compat: the bundle does not yet exist on disk; when it lands the same rules apply).

If a bundle-specific layer folder (`Dto/`, `Parser/`, `Validator/`, `View/`, `Dto/Embedded/`) does not exist, create it the first time you need it. Never place code outside the layer it belongs to.

---

## 2. HTTP method → attribute mapping (three-way rule, no exceptions)

| HTTP method | Mapping attribute | DTO required? | CSRF wiring |
|---|---|---|---|
| `GET` | `#[MapQueryString]` (only when the action reads query parameters beyond simple path IDs) | typed DTO argument | none (idempotent) |
| `POST` / `PUT` / `PATCH` | `#[MapRequestPayload]` | typed DTO argument | first-line `isTokenValid(new CsrfToken(<intention>, $dto->csrfToken))` |
| `DELETE` | **NONE** — no mapping attribute | identifiers via URL path only (`int $id`, `#[MapEntity] Ads $ads`) | slim single-field CSRF DTO on a custom `#[ValueResolver]` — see §8 |

FQCNs (import at the top of every file that uses them):

- `Symfony\Component\HttpKernel\Attribute\MapRequestPayload`
- `Symfony\Component\HttpKernel\Attribute\MapQueryString`
- `Symfony\Bridge\Doctrine\Attribute\MapEntity` (when a `DELETE` or any other route resolves a Doctrine entity from a path param)

Hard rules:

- `DELETE` MUST NOT carry `#[MapRequestPayload]` or `#[MapQueryString]`. `DELETE` has no body and no query payload — path params only.
- A `GET` that touches nothing beyond its path params carries no mapping attribute (a plain `int $id` argument is fine).
- `POST` / `PUT` / `PATCH` ALWAYS carry `#[MapRequestPayload]` on their DTO argument.

---

## 3. Attribute options catalogue

Fetched verbatim from the Symfony 8.1+ docs:

| Option | Type | Applies to | Purpose |
|---|---|---|---|
| `acceptFormat` | `'json' \| 'form' \| null` | `MapRequestPayload` | Force a body denormaliser: `'json'` for JSON bodies, `'form'` for `application/x-www-form-urlencoded` **and** `multipart/form-data`. `null` (default) infers from `Content-Type`. |
| `validationGroups` | `string[] \| GroupSequence \| null` | both | Validation groups to run against the hydrated DTO. |
| `validationFailedStatusCode` | `int` | both | Default `422` for `MapRequestPayload`, `404` for `MapQueryString`. Codebase preserves defaults. |
| `resolver` | FQCN | both | Custom resolver; default `Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestPayloadValueResolver`. Do not override in this codebase. |
| `mapWhenEmpty` | `bool` | both | Default `false` — an entirely empty body/query yields the default value / `null`. Codebase relies on the default. |
| `serializationContext` | `array` | both | Extra denormalization context. Rarely needed. |
| `key` | `string` | `MapRequestPayload` | Subkey of the payload to hydrate from (e.g. `payload[user]`). Not used in this codebase. |
| `type` | `string \| null` | `MapRequestPayload` | Element type hint for `array<Item>` shapes — required when the DTO property is a typed collection of nested DTOs / `UploadedFile`. |

### Codebase convention

- **JSON body:** bare `#[MapRequestPayload]` (Content-Type `application/json`).
- **Form / multipart body:** `#[MapRequestPayload(acceptFormat: 'form')]` (Content-Type `application/x-www-form-urlencoded` or `multipart/form-data`). Precedent: `SendReservationRequest`, `SetNewPasswordRequest`.
- **GET filter / list:** `#[MapQueryString]`. Precedent: `IndexRequest`, `LogoTrackingRequest`, `CheckAdsExistRequest`.

### Denormalization vs. validation vs. wrong Content-Type — exception mapping

| Failure mode | HTTP status | Exception |
|---|---|---|
| Wrong `Content-Type` | 415 | `HttpException` (previous: `UnsupportedMediaTypeHttpException`) |
| Malformed body / denormalization | 400 | `HttpException` (previous: `NotEncodableValueException` or similar) |
| Validation failure (`MapRequestPayload`) | 422 | `HttpException` (previous: `ValidationFailedException`) |
| Validation failure (`MapQueryString`) | 404 | `HttpException` (previous: `ValidationFailedException`) |

All raised failures are `HttpException` subclasses; validation failures carry a `ValidationFailedException` as `$previous`. The kernel exception listener in §12 catches them and rewrites the body per-endpoint to preserve legacy failure JSON shapes byte-for-byte.

---

## 4. DTO shape rules

### Location

| Bundle | Path |
|---|---|
| AdminBundle | `src/AdminBundle/Dto/<Feature>/<Action>Request.php` |
| SiteBundle | `src/SiteBundle/Dto/<Feature>/<Action>Request.php` |
| LogBundle | `src/LogBundle/Dto/<Feature>/<Action>Request.php` |

### Class shape

- `final` class.
- Typed **public** properties with sensible defaults (`= ''`, `= 0`, `= 0.0`, `= []`, `= null`). No getters/setters — DTOs are dumb data carriers.
- `Symfony\Component\Validator\Constraints as Assert` attributes on every shape-significant property.
- One DTO **per resource**, shared between Create and Update. Insert-vs-update Assert divergence is handled via Symfony validation groups (`#[Assert\NotBlank(groups: ['insert'])]` on required-on-insert-only fields; controllers pass `#[MapRequestPayload(validationGroups: ['Default', 'insert'])]` on the create endpoint and `['Default', 'update']` on the update endpoint). When Insert and Update Assert profiles are 100% identical, no groups are needed — one Assert set runs on both endpoints. See `AdminBundle\Dto\User\UserSaveRequest` for the groups pattern; `SiteBundle\Dto\Ads\AdsSaveRequest` for the identical-profile no-groups pattern.
- No constructor when the DTO has only scalar / nullable properties (Symfony instantiates via reflection). Add a **small `__construct()`** ONLY to initialize nested embedded DTOs — see §10.

### Bag-type ban

Every one of the following types is **forbidden** as a DTO property, EVER:

- `Symfony\Component\HttpFoundation\ParameterBag`
- `Symfony\Component\HttpFoundation\InputBag`
- `Symfony\Component\HttpFoundation\HeaderBag`
- `Symfony\Component\HttpFoundation\FileBag`
- `Symfony\Component\HttpFoundation\ServerBag`
- `Symfony\Component\HttpFoundation\Request`

Every request field the action needs is an explicit typed DTO property.

### Wire-key preservation

When the on-the-wire field name differs from the PHP property name, use `#[Symfony\Component\Serializer\Attribute\SerializedName('<wire_key>')]` on the property. Never rename on the wire.

### Before / after

**Before** (anti-pattern — bag-holding DTO):

```php
final class AdsInsertRequest
{
    public function __construct(public ParameterBag $body) {}
}
```

**After** (canonical typed DTO — flat wire ⇒ flat DTO properties; only the `contact[*]` bracket-notation sub-tree lives as an embedded `AdsContactDto` because the wire genuinely nests there):

```php
namespace SiteBundle\Dto\Ads;

use SiteBundle\Dto\Embedded\AdsContactDto;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class AdsSaveRequest
{
    #[Assert\NotBlank]
    #[SerializedName('_csrf_token')]
    public string $csrfToken = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[SerializedName('title_rs')]
    public string $title = '';

    #[Assert\NotBlank]
    #[SerializedName('description_rs')]
    public string $description = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[SerializedName('_address')]
    public string $address = '';

    #[Assert\Positive]
    public int $category = 0;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $city = '';

    #[Assert\Type('numeric')]
    #[Assert\Range(min: -90, max: 90)]
    public float $lat = 0.0;

    #[Assert\Type('numeric')]
    #[Assert\Range(min: -180, max: 180)]
    public float $lng = 0.0;

    #[Assert\PositiveOrZero]
    #[SerializedName('post_price_from')]
    public int $postPriceFrom = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('post_price_to')]
    public int $postPriceTo = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('pre_price_from')]
    public int $prePriceFrom = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('pre_price_to')]
    public int $prePriceTo = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('price_from')]
    public int $priceFrom = 0;

    #[Assert\PositiveOrZero]
    #[SerializedName('price_to')]
    public int $priceTo = 0;

    #[Assert\Length(max: 500)]
    public ?string $facebook = null;

    #[Assert\Length(max: 500)]
    public ?string $website = null;

    #[Assert\Length(max: 500)]
    public ?string $instagram = null;

    public string $youtube = '[]';

    public string $documents = '[]';

    /** @var array<string, array<int, string>> */
    public array $tags = [];

    #[Assert\Positive]
    #[SerializedName('price_plan')]
    public int $pricePlan = 0;

    #[SerializedName('payment_date')]
    public ?string $paymentDate = null;

    #[Assert\Valid]
    public AdsContactDto $contact;

    public function __construct()
    {
        $this->contact = new AdsContactDto();
    }
}
```

Consolidated wire DTO for BOTH `POST /api/product` and `PUT /product/{id}` — 26 flat typed properties (CSRF token, title, description, address, category, city, coordinates, post/pre/base price ranges, social links, youtube/documents/tags payloads, price plan, payment date) plus one embedded `AdsContactDto`. Assert profiles are byte-for-byte identical between the two endpoints, so **no `validationGroups` are needed** on either `#[MapRequestPayload]` — a single Assert set runs on both. When Assert profiles diverge, see the "Consolidated save-request with validation groups" worked example below.

### Consolidated save-request with validation groups (canonical shape when Assert profiles diverge)

When Insert and Update Assert profiles diverge (e.g. `email` + `password` required on create but optional on update), consolidate into a single `<Resource>SaveRequest` with nullable typing and per-endpoint validation groups. Real example: `AdminBundle\Dto\User\UserSaveRequest`.

```php
namespace AdminBundle\Dto\User;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class UserSaveRequest
{
    #[SerializedName('_csrf_token')]
    #[Assert\NotBlank]
    public string $csrfToken = '';

    #[SerializedName('first_name')]
    #[Assert\Length(max: 100)]
    public ?string $firstName = null;

    #[SerializedName('last_name')]
    #[Assert\Length(max: 100)]
    public ?string $lastName = null;

    #[Assert\NotBlank(groups: ['insert'])]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank(groups: ['insert'])]
    #[Assert\Length(min: 6)]
    public ?string $password = null;

    #[Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])]
    public ?string $role = null;
}
```

Paired controller signatures — the group name flips per endpoint:

```php
#[Route('/api/add-user', name: 'admin.add_user_api', methods: ['POST'])]
public function add(
    Request $request,
    #[MapRequestPayload(acceptFormat: 'form', validationGroups: ['Default', 'insert'])] UserSaveRequest $dto,
): JsonResponse {
    // …
}

#[Route('/api/update-user/{id}', name: 'admin.edit_user_api', methods: ['PUT'])]
public function update(
    Request $request,
    int $id,
    #[MapRequestPayload(acceptFormat: 'form', validationGroups: ['Default', 'update'])] UserSaveRequest $dto,
): JsonResponse {
    // …
}
```

- `NotBlank(groups: ['insert'])` fires only on the create endpoint (because `['Default', 'insert']` includes the `insert` group).
- The unconstrained `Email` + `Length(min: 6)` Asserts run on both endpoints via the implicit `Default` group — they fire when a non-null value is submitted regardless of the group set.
- Update-only omissions (empty `email`, empty `password`) pass validation silently — the parser branches on `null !== $dto->email` before touching the entity.

Group-name convention across the codebase: `['insert']` for CRUD create endpoints, `['update']` for CRUD update endpoints, `['register']` for the public site's registration endpoint (see `SiteBundle\Dto\User\UserSaveRequest`).

---

## 5. Validation via Assert attributes

Every shape rule lives on the DTO as an Assert attribute. Symfony runs the validator automatically before your action body executes; failure raises `ValidationFailedException` wrapped in an `HttpException` (422 for `MapRequestPayload`, 404 for `MapQueryString`).

Primary constraints used across the codebase:

- `#[Assert\NotBlank]` — required scalar string / int / etc.
- `#[Assert\NotNull]` — required non-scalar (object, `UploadedFile`, nested DTO).
- `#[Assert\Length(min: ..., max: ...)]` — string length bounds.
- `#[Assert\Email]` — email format.
- `#[Assert\Type('numeric')]`, `#[Assert\Type('int')]`, `#[Assert\Type(UploadedFile::class)]` — type-shape.
- `#[Assert\Count(min: ..., max: ...)]` — collection size.
- `#[Assert\Valid]` — cascade validation into a nested DTO / collection of DTOs.
- `#[Assert\Range(min: ..., max: ...)]` — numeric bounds.
- `#[Assert\Positive]`, `#[Assert\PositiveOrZero]`.
- `#[Assert\Regex('/^.../')]` — pattern.
- `#[Assert\Choice(choices: [...])]` — enum-like fields.
- `#[Assert\Image(maxSize: '5M', mimeTypes: [...])]` — image upload rules.
- `#[Assert\File(maxSize: '10M', mimeTypes: [...])]` — generic file upload rules.
- `#[Assert\All([new Assert\Image(...)])]` — per-item rules on a collection.

### Worked DTO

```php
namespace SiteBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ContactFormRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 120)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 200)]
    public string $subject = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 5000)]
    public string $message = '';

    public string $website = '';
}
```

---

## 6. Validator-layer retirement (with residual case)

The separate `Validator/` class layer that used to wrap `ValidatorInterface` and return `array<string, string>` is **retired for the common case**. Every shape rule now lives on the DTO via Assert attributes and is checked by Symfony's automatic pre-controller validation.

### Residual case — cross-field domain rules

A separate `ConstraintValidator` class is still justified for rules the Assert built-ins cannot express — most commonly **cross-field business rules on an entity** (e.g. "publish requires all locale copies non-empty", or "checkout must be strictly after checkin"). Attach the custom constraint at the class level and pair it with a `ConstraintValidator` subclass.

Worked example — "checkout after checkin" on a reservation:

```php
namespace SiteBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class CheckoutAfterCheckin extends Constraint
{
    public string $message = 'Checkout date must be after checkin date.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
```

```php
namespace SiteBundle\Validator;

use SiteBundle\Entity\Reservation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CheckoutAfterCheckinValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (false === $value instanceof Reservation) {
            return;
        }

        if ($value->getCheckout() > $value->getCheckin()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->atPath('checkout')
            ->addViolation();
    }
}
```

Then on the entity:

```php
#[CheckoutAfterCheckin]
class Reservation { ... }
```

No manual `Validator/` wrapper class is created. The controller does NOT invoke this validator directly — it fires automatically when `#[MapRequestPayload]` calls Symfony's validator on the DTO, OR when the Parser flushes the entity through a handler that runs its own domain validation.

---

## 7. CSRF for POST / PUT / PATCH

### DTO side

Every DTO backing a state-changing action carries **exactly one** CSRF property:

```php
#[SerializedName('<wire_key>')]
#[Assert\NotBlank]
public string $csrfToken = '';
```

- Property name is **always** `$csrfToken` (PHP side).
- On-wire key is preserved per-endpoint via `#[SerializedName]`. Existing wire keys in this codebase: `_csrf_token` (most endpoints), `token` (reservation). NEVER rename.
- `#[Assert\NotBlank]` is the ONLY CSRF-related attribute. Forbidden: `#[Assert\CsrfToken]`, `#[ValidCsrfToken]`, `#[CsrfValid]`, any custom CSRF `ConstraintValidator`, any pre-controller CSRF listener.
- The DTO holds the value; it does NOT verify it.

### Controller side

Constructor-inject `Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $csrfTokenManager`. The **first executable statement** of the action body is the verification call:

```php
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

public function insert(
    #[MapRequestPayload(acceptFormat: 'form')] AdsInsertRequest $dto,
): JsonResponse {
    if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_ad', $dto->csrfToken))) {
        throw $this->createAccessDeniedException();
    }

    $ads = $this->adsInsertRequestParser->parse($dto);
    $this->handler->save($ads);

    return new JsonResponse($this->view->view($ads), Response::HTTP_CREATED);
}
```

- Use Yoda (`false === ...`).
- Use a real `throw`. The historical bug in the codebase (`$this->createAccessDeniedException();` without `throw`) is a no-op — never copy it.
- One visible line, no wrapper method. Reviewers can spot the CSRF check at a glance.
- **The CSRF token MUST NEVER be read via `$request->request->get(...)`, `$request->headers->get(...)`, `$request->getContent()`, or any other request accessor.** Only `$dto->csrfToken`.
- CSRF intention IDs are per-endpoint and preserved one-for-one from the Twig `csrf_token(...)` call sites (`set_ad`, `ad_reservation`, `info_page_edit`, `set_user`, etc.). No cross-endpoint unification.

### Before / after — CSRF migration

**Before** (raw request read + broken no-op):

```php
public function insert(Request $request): JsonResponse
{
    if (false === $this->isCsrfTokenValid('set_ad', $request->request->get('_csrf_token'))) {
        $this->createAccessDeniedException();
    }

    $body = new ParameterBag($request->request->all());
    // ...
}
```

**After** (typed DTO + real throw + first-line verification):

```php
public function insert(
    #[MapRequestPayload(acceptFormat: 'form')] AdsInsertRequest $dto,
): JsonResponse {
    if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_ad', $dto->csrfToken))) {
        throw $this->createAccessDeniedException();
    }

    // ...
}
```

---

## 8. CSRF for DELETE

`DELETE` actions carry no body and no query payload. If a `DELETE` endpoint needs CSRF (none in this codebase carries it today — CSRF-for-DELETE is documented here for future use), use the following pattern:

**Pattern:** single-field CSRF-only DTO argument on the `DELETE` action, hydrated via a **custom `#[ValueResolver]`** (NOT `#[MapQueryString]` — spec-forbidden on `DELETE`). Controller performs the same first-line `isTokenValid(...)` check.

### DTO

```php
namespace SiteBundle\Dto\Delete;

use Symfony\Component\Validator\Constraints as Assert;

final class DeleteCsrfTokenDto
{
    #[Assert\NotBlank]
    public string $csrfToken = '';
}
```

### Value resolver

```php
namespace SiteBundle\ValueResolver;

use SiteBundle\Dto\Delete\DeleteCsrfTokenDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class DeleteCsrfTokenResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (DeleteCsrfTokenDto::class !== $argument->getType()) {
            return [];
        }

        $dto = new DeleteCsrfTokenDto();
        $dto->csrfToken = (string) ($request->query->get('_csrf_token') ?? $request->headers->get('X-CSRF-Token', ''));

        yield $dto;
    }
}
```

### Controller

```php
use Symfony\Component\HttpKernel\Attribute\ValueResolver;

#[Route('/api/ads/{id}', methods: ['DELETE'], requirements: ['id' => '\d+'])]
public function remove(
    #[MapEntity(id: 'id')] Ads $ads,
    #[ValueResolver(DeleteCsrfTokenResolver::class)] DeleteCsrfTokenDto $csrf,
): JsonResponse {
    if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('delete_ad', $csrf->csrfToken))) {
        throw $this->createAccessDeniedException();
    }

    // ...
}
```

- `#[MapQueryString]` on `DELETE` is **forbidden** — even for CSRF-only DTOs.
- `$request->query->get(...)` / `$request->headers->get(...)` reads inside the action are **forbidden** — always through the typed resolver.
- **NOTHING is wired to this pattern in the current codebase.** No live DELETE endpoint carries CSRF. Adding CSRF to any DELETE endpoint is a behavior change and requires an explicit approval.

### Second worked example — non-CSRF typed query token

Some `DELETE` endpoints carry a non-CSRF confirmation guard (e.g. `?confirm=obriši`). This is NOT CSRF; it is a human-confirmation surrogate. Same mechanism (custom `#[ValueResolver]`), same forbid on `#[MapQueryString]`.

```php
namespace AdminBundle\ValueResolver;

use AdminBundle\Dto\InfoPage\InfoPageRemoveDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class InfoPageRemoveConfirmValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (InfoPageRemoveDto::class !== $argument->getType()) {
            return [];
        }

        $dto = new InfoPageRemoveDto();
        $dto->confirm = (string) $request->query->get('confirm', '');
        $dto->id = (int) $request->attributes->get('id', 0);

        yield $dto;
    }
}
```

The controller then performs a first-line `if ('obriši' !== $dto->confirm) { throw $this->createAccessDeniedException(); }` check — same shape as CSRF, different semantics.

---

## 9. Parser layer — Parsers survive; refactored, never deleted

### Pipeline

```
DTO → Controller → Parser → Save Handler
```

The Parser's **sole role** post-migration: `DTO → Entity` transformation (or `DTO → domain value object` for degenerate cases like image-resize orchestration).

### Survives-vs-retirable distinction

Files that ARE Parser classes — **survive** the migration, refactored in-place:

- `src/SiteBundle/Parser/*.php` (`AdsInsertRequestParser`, `AdsUpdateRequestParser`, `AdsEditParser`, `SearchDataParser`, `RegisterUserRequestParser`, `UpdateUserRequestParser`, `ResizeImageRequestParser`, `ContactFormRequestParser`, `SendReservationRequestParser`, `SetNewPasswordRequestParser`, `AdsContactUserParser`, `AdsTagParser`, `AdsPayedDateParser`, `YouTubeParser`, `InfoPageTagParser`, `UserToRoleParser`, `AdsListRequestParser`).
- `src/AdminBundle/Parser/*.php` (`InfoPageEditRequestParser`, `ProductEditRequestParser`, `ProductPaymentRequestParser`, `DataTableRequestParser`, `Admin/UserEditRequestParser`).

Files that are NOT Parser classes and MAY be retired if superseded:

- `Symfony\Component\HttpKernel\Controller\ValueResolverInterface` implementations under `src/SiteBundle/Parser/Resolver/` — natively replaced by `#[MapRequestPayload]` / `#[MapQueryString]`. **Retired.**
- Marker interfaces (e.g. `AdminBundle\Parser\RequestParserInterface`) — **refactored** if they have ≥ 2 concrete implementers AND real DI cross-cutting abstraction; **retired** otherwise.
- Helper traits (e.g. `AdminBundle\Parser\ParserTrait`) — **retired** when the consumer count is ≤ 1; the surviving consumer inlines the logic (or drops it entirely if the DTO shape makes it redundant).

### Post-migration Parser shape

- `final` class.
- Constructor DI for collaborators (repositories, hashers, child parsers, `TextHelper`, `EntityManagerInterface` — read-only).
- Single public method — typical shapes:
  - `parse(<Dto> $dto): <Entity>` (create action — builds new entity).
  - `parse(<Dto> $dto, <Entity> $entity): <Entity>` (update action — mutates existing entity).
  - `parse(<InsertDto>|<UpdateDto> $dto, ?<Entity> $entity = null): <Entity>` (parser shared by create + update).
  - `parse(<Dto> $dto): <DomainValueObject>` (degenerate case — image resize returns a `PendingResize` value object).
- **No HTTP types** (`Request`, `ParameterBag`, `Session` — forbidden).
- **No persistence calls** — `EntityManagerInterface::persist/flush` lives in the Handler.
- **No validator calls** — Assert-on-DTO runs automatically pre-controller.
- **No logging** — parsers are pure data translation.

### Before / after — Parser refactor

**Before** (raw request reads + bag-holding DTO):

```php
final class AdsInsertRequestParser
{
    public function fromRequest(Request $request): AdsInsertRequest
    {
        return new AdsInsertRequest(new ParameterBag($request->request->all()));
    }
}
```

**After** (typed DTO in, entity out):

```php
final class AdsInsertRequestParser
{
    public function __construct(
        private readonly AdsEditParser $adsEditParser,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public function parse(AdsInsertRequest $dto): Ads
    {
        $ads = new Ads();
        $user = $this->tokenStorage->getToken()?->getUser();

        $this->adsEditParser->update($ads, $dto, $user);

        return $ads;
    }
}
```

Note the **file is preserved** — same class name, same constructor DI backbone, only the method signature and internals change.

### Refactored interface — `AdminBundle\Parser\RequestParserInterface`

The historical interface signature was `parse(ParameterBag $bag, ?EntityInterface $entity = null): EntityInterface`. Post-migration it is:

```php
namespace AdminBundle\Parser;

use SiteBundle\Entity\EntityInterface;

interface RequestParserInterface
{
    /**
     * @param object $dto Concrete implementers narrow the accepted DTO in their own docblock.
     * Return type MAY be narrowed via LSP covariance.
     */
    public function parse(object $dto, ?EntityInterface $entity = null): EntityInterface;

    public function create(): EntityInterface;
}
```

Concrete implementers narrow the accepted DTO type in their own docblock and MAY covariantly narrow the return type to a concrete `EntityInterface` subclass.

---

## 10. Embedded / shared DTOs

### Location

```
src/<Bundle>/Dto/Embedded/
```

- **Flat** — no per-domain sub-folder inside `Embedded/`.
- **Bundle-local** — each bundle owns its own copy. Even when the shape is identical (e.g. a `CoordinatesDto` in both `SiteBundle` and `AdminBundle`), do NOT extract cross-bundle.

### Naming

`<Cluster>Dto` — PascalCase noun cluster. The two canonical survivors post-consolidation: `AdsContactDto`, `DataTableQueryDto`.

### Shape

- `final` class.
- Typed public properties with defaults (`string $x = ''`, `?string $y = null`, `float $lat = 0.0`).
- Assert attributes on their own lines above the property.
- **NO constructor promotion** for embedded DTOs — it clashes with the denormaliser's property-access path used to hydrate nested objects.
- Cascade validation is triggered by `#[Assert\Valid]` on the parent DTO's embedded property.

### Nested-DTO instantiation

The denormaliser needs a live instance to populate. Give the parent DTO a small constructor that news-up the embedded DTO. Real on-disk shape (`SiteBundle\Dto\Ads\AdsSaveRequest`):

```php
class AdsSaveRequest
{
    #[Assert\Valid]
    public AdsContactDto $contact;

    public function __construct()
    {
        $this->contact = new AdsContactDto();
    }
}
```

If a parent DTO carries more than one genuinely-nested embedded sub-tree, add one `new <EmbeddedDto>()` line per property in the same constructor — the denormaliser will populate each one from its wire sub-tree.

### Reused across DTOs and parsers

```php
namespace SiteBundle\Dto\Embedded;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class AdsContactDto
{
    #[SerializedName('contact_person')]
    #[Assert\Length(max: 200)]
    public ?string $contactPerson = null;

    #[SerializedName('contact_email')]
    #[Assert\Email]
    #[Assert\Length(max: 200)]
    public ?string $contactEmail = null;

    #[SerializedName('contact_phone')]
    #[Assert\Length(max: 50)]
    public ?string $contactPhone = null;
}
```

Consumed by `AdsSaveRequest` (constructor news-up + `#[Assert\Valid]`):

```php
final class AdsSaveRequest
{
    #[Assert\Valid]
    public AdsContactDto $contact;

    public function __construct()
    {
        $this->contact = new AdsContactDto();
    }
}
```

And by `AdminBundle\Parser\AdsContactUserParser::parse` as a typed parameter (second consumer — the parser accepts the already-hydrated embedded DTO directly and translates its three fields onto the `Ads` entity's `Contact` value-object):

```php
final class AdsContactUserParser
{
    public function parse(AdsContactDto $contact, Ads $ads): void
    {
        // …
    }
}
```

Both consumers exercise the same bracket-notation `contact[*]` wire sub-tree — genuinely nested, ≥ 2 consumers, extraction is justified.

An embedded DTO is only worth extracting when **all three** conditions hold: (i) the wire structure is **genuinely nested** — a bracket-notation sub-tree (e.g. `contact[email]`) OR a JSON array of objects OR a JSON sub-object — such that the denormaliser would produce a nested PHP structure regardless of DTO shape; (ii) the same cluster is reused ≥ 2× across parent DTOs OR the cluster carries cross-field validation (e.g. `checkOut > checkIn`); (iii) leaving it flat on the parent would materially hurt readability. **Flat wire clusters (e.g. `lat`/`lng`, `post_price_*`/`pre_price_*`, `facebook`/`website`/`instagram`) live flat on the parent DTO — never extract them into an embedded DTO even if reused, because doing so requires a custom denormaliser to rebind the flat wire keys into the nested PHP shape.** See `SiteBundle\Dto\Embedded\AdsContactDto` (bracket-notation `contact[*]` — 3 consumers, genuinely nested) and `AdminBundle\Dto\Embedded\DataTableQueryDto` (shared DataTables 1.10+ query envelope — 4 consumers, genuinely nested) for the canonical shapes.

---

## 11. Multipart uploads

Symfony 8.1+'s `#[MapRequestPayload(acceptFormat: 'form')]` covers `multipart/form-data` natively — the form denormaliser hydrates both scalar fields AND `UploadedFile` properties from the multipart body in one shot.

### DTO property shapes

| Form field | DTO property | Constraint |
|---|---|---|
| Single file (`<input type="file" name="hostPhoto">`) | `public ?UploadedFile $hostPhoto = null;` | `#[Assert\Image(maxSize: '5M', mimeTypes: [...])]` |
| Multi indexed (`<input type="file" name="images[]" multiple>`) | `/** @var array<int, UploadedFile> */ public array $images = [];` + `#[Assert\All([new Assert\Image(...)])]` + `#[Assert\Count(max: 20)]` | inner `Assert\Image` + outer `Assert\Count` |
| Multi named-key (`<input type="file" name="images[main]">`, `<input type="file" name="images[side]">`) | `/** @var array<string, UploadedFile> */ public array $images = [];` + `#[Assert\All([new Assert\Image(...)])]` | `Assert\All` |

Rules:

- Type is `Symfony\Component\HttpFoundation\File\UploadedFile` (or `?UploadedFile` for optional single, `array` for collections — element type via `@var` PHPDoc annotation).
- Always attach `Assert\Image` / `Assert\File` with `maxSize`, `mimeTypes`, and (for images) `maxWidth` / `maxHeight` limits.
- Use `#[Assert\NotNull]` on a required single upload; omit for optional.
- The controller signature is `#[MapRequestPayload(acceptFormat: 'form')]` — NOT the default JSON mode.
- **No `$request->files->get(...)` anywhere in the controller.**
- **No separate Parser for multipart** — the Parser receives the DTO whose file properties are already populated.

### Worked example — single-file upload

```php
namespace SiteBundle\Dto\Ads;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class ResizeImageRequest
{
    #[SerializedName('tmp_image')]
    #[Assert\NotNull]
    #[Assert\Image(maxSize: '10M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    public ?UploadedFile $file = null;
}
```

```php
#[Route('/api/ads/resize-image-on-fly', methods: ['POST'])]
public function resizeImageOnFlyAction(
    #[MapRequestPayload(acceptFormat: 'form')] ResizeImageRequest $dto,
): JsonResponse {
    $result = $this->resizeImageRequestParser->parse($dto);

    return new JsonResponse($result);
}
```

---

## 12. Failure-shape preservation

Every migrated endpoint's failure JSON body MUST match the pre-refactor shape byte-for-byte. Symfony's default responses for `HttpException` (422/400/415) are translated back per-endpoint by ONE framework-level mechanism.

### Chosen mechanism — Option A: kernel exception listener

Location: `src/SiteBundle/EventListeners/PayloadMappingFailureListener.php` (SiteBundle owns global listeners; AdminBundle routes matched by `_route` prefixes).

Shape:

- `final` class, tagged via `#[AsEventListener(event: 'kernel.exception', priority: 128)]`.
- `__invoke(ExceptionEvent $event): void`:
  1. Grab `$exception = $event->getThrowable()`.
  2. Return early unless `$exception instanceof HttpException` AND `$exception->getStatusCode() in [400, 415, 422]`.
  3. Distinguish validation-failure (`$exception->getPrevious() instanceof ValidationFailedException`) from denormalization / content-type.
  4. `$route = $event->getRequest()->attributes->get('_route')`.
  5. `match ($route) { ... }` dispatch to a per-shape formatter method — `formatOkFalseViolations()`, `formatOkFalseError()`, `formatErrorMsg()`, `formatEmptyRequest()`, `formatMsgThrowable()`, `formatContactShape()`, `formatBrackets()`, `formatNull()`. Unknown route ⇒ do NOT `setResponse` (let Symfony render its default).
  6. `$event->setResponse(new JsonResponse($body, $status))`.

### Rejected options — recorded here so future readers do not re-litigate

- **Option B: per-endpoint `try/catch` inside each controller.** Rejected — violates the thin-controller rule (business/error logic bleeds into HTTP glue).
- **Option C: custom `ValueResolver` wrapping `RequestPayloadValueResolver`.** Rejected — wraps Symfony internals and repeats per attribute-usage; harder review surface than one listener class.

### Before / after — failure JSON parity

**Before** (per-endpoint try/catch in controller):

```php
try {
    $body = json_decode($request->getContent(), true);
    // manual validation
} catch (\Throwable $e) {
    return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
}
```

**After** (framework hydration + listener rewrites the 422 to the legacy shape):

```php
public function submit(#[MapRequestPayload] ContactFormRequest $dto): JsonResponse
{
    $submission = $this->contactFormRequestParser->parse($dto);
    $this->contactHandler->send($submission);

    return new JsonResponse(['status' => 'ok']);
}
```

The listener's `formatContactShape()` catches the 422 raised by `#[MapRequestPayload]`'s validator and rewrites the body to `{status:'error', message:<first violation>, errors:{<field>:<message>}}` — byte-for-byte the legacy shape.

---

## 13. Worked examples

### 13.1 Form POST with embedded DTO

```php
namespace SiteBundle\Dto\Ads;

use SiteBundle\Dto\Embedded\AdsContactDto;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class AdsSaveRequest
{
    #[SerializedName('_csrf_token')]
    #[Assert\NotBlank]
    public string $csrfToken = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[SerializedName('title_rs')]
    public string $title = '';

    // … 24 more flat typed properties (see §4 "After" example) …

    #[Assert\Valid]
    public AdsContactDto $contact;

    public function __construct()
    {
        $this->contact = new AdsContactDto();
    }
}
```

```php
#[Route('/api/product', name: 'site_ads_save', methods: ['POST'])]
public function insert(#[MapRequestPayload(acceptFormat: 'form')] AdsSaveRequest $dto): JsonResponse
{
    if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_ad', $dto->csrfToken))) {
        throw $this->createAccessDeniedException();
    }

    $ads = $this->adsSaveRequestParser->parse($dto);
    $this->adsHandler->save($ads);

    return new JsonResponse($this->view->view($ads), Response::HTTP_CREATED);
}
```

The embedded `AdsContactDto` models the bracket-notation `contact[contact_person]`, `contact[contact_email]`, `contact[contact_phone]` wire sub-tree — genuinely nested per §10, so extraction is justified. Every other cluster (coordinates, price plans, social links) stays flat on the parent because the wire keys are flat (`lat`, `lng`, `post_price_from`, `facebook`, …).

### 13.2 GET filter

```php
namespace SiteBundle\Dto\Ads;

use Symfony\Component\Validator\Constraints as Assert;

final class AdsListRequest
{
    #[Assert\Length(max: 100)]
    public ?string $category = null;

    #[Assert\Length(max: 100)]
    public ?string $city = null;

    #[Assert\Length(max: 200)]
    public ?string $q = null;

    #[Assert\Range(min: 1, max: 500)]
    public int $page = 1;

    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 20;
}
```

```php
#[Route('/api/ads', methods: ['GET'])]
public function indexAction(#[MapQueryString] ?AdsListRequest $dto = null): JsonResponse
{
    $criteria = $this->adsListRequestParser->parse($dto ?? new AdsListRequest());

    return new JsonResponse($this->view->view($this->adsHandler->list($criteria)));
}
```

### 13.3 Multipart upload

See §11 (`ResizeImageRequest` + controller).

### 13.4 DELETE + slim CSRF

See §8.

### 13.5 Before / after — CSRF property migration

See §7 "Before / after — CSRF migration".

### 13.6 Before / after — Parser refactor

See §9 "Before / after — Parser refactor".

---

## 14. Layer 4 — Entity-level Validator (residual)

Only relevant when the residual case in §6 applies. When it does:

- `src/<Bundle>/Validator/<Rule>.php` — the constraint (`Attribute::TARGET_CLASS`).
- `src/<Bundle>/Validator/<Rule>Validator.php` — the `ConstraintValidator` subclass.

**Do NOT use `src/SiteBundle/Validators/` (plural-s).** That is the legacy `ValidatorContainer` array-based system from 2017. The canonical pattern uses `Validator/` (singular) and Symfony's `ConstraintValidator` subclass.

The controller does NOT invoke this validator directly — attach the constraint to the entity via `#[Attribute]`, and the validator fires whenever the entity is validated (by the Handler, or by the Save-flow's flush).

---

## 15. Layer 5 — View (`src/<Bundle>/View/`)

### Where

| Bundle | Path |
|---|---|
| AdminBundle | `src/AdminBundle/View/<Entity>View.php` (create if missing) |
| SiteBundle | `src/SiteBundle/View/<Entity>View.php` (already exists) |

### Shared interface — `SiteBundle\View\ViewInterface`

Lives in SiteBundle because `EntityInterface` lives there.

```php
namespace SiteBundle\View;

use SiteBundle\Entity\EntityInterface;

interface ViewInterface
{
    /** @return array<string, mixed> */
    public function view(EntityInterface $entity): array;
}
```

### View class shape

- `final` class implementing `ViewInterface`.
- `view(EntityInterface $entity): array` — verify concrete type via `instanceof`, throw `InvalidArgumentException` on mismatch.
- Returns **all** persisted properties of the entity: id, scalars, datetimes as ISO strings, FK ids/aliases, status, timestamps.
- Nested entities → delegate to their own `<NestedEntity>View` (constructor-injected).
- Constructor DI for collaborators (router, translator, child views, purifier, helpers).
- Additional helper methods permitted (see `src/SiteBundle/View/AdView.php`).
- Date formatting, slugification, URL generation, HTML purification — all here, NOT in the controller and NOT in the entity.

### Reusing SiteBundle views

Shared domain entities (`Category`, `Media`, `Tag`, `Contact`, `Adshastags`, etc.) already have views in `src/SiteBundle/View/`. **Reuse them.** Do not duplicate an `AdminBundle\View\CategoryView` if `SiteBundle\View\CategoryView` already exists.

### Controller usage — JSON

```php
return new JsonResponse($this->view->view($entity), Response::HTTP_CREATED);
```

### Controller usage — Twig

```php
return $this->render('@Admin/Pages/productEdit.html.twig', [
    'product' => $this->view->view($entity),
    'languages' => $this->languages,
]);
```

For composite Twig pages backed by multiple entities, keep the existing `*ResponseFormatter` pattern (`src/AdminBundle/Formatter/InfoPageEditResponseFormatter.php`) — the Formatter composes View outputs and adds page-level context (form options, language lists, URLs). Each entity field in the final array must still flow through its View, not be hand-built.

---

## 16. Controller skeleton — copy-paste reference

```php
<?php

declare(strict_types=1);

namespace AdminBundle\Controller\Products\Api;

use AdminBundle\Dto\Product\ProductSaveRequest;
use AdminBundle\Parser\ProductEditRequestParser;
use AdminBundle\View\ProductView;
use SiteBundle\Handler\AdsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ProductEditController extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly ProductEditRequestParser $parser,
        private readonly AdsHandler $handler,
        private readonly ProductView $view,
    ) {
    }

    #[Route('/admin/api/product', name: 'admin.api.product.insert', methods: ['POST'])]
    public function insert(
        #[MapRequestPayload(acceptFormat: 'form')] ProductSaveRequest $dto,
    ): JsonResponse {
        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('set_ad', $dto->csrfToken))) {
            throw $this->createAccessDeniedException();
        }

        $ads = $this->parser->parse($dto);
        $this->handler->save($ads);

        return new JsonResponse($this->view->view($ads), Response::HTTP_CREATED);
    }
}
```

Notes:

- No `Request $request` argument.
- No `$request->request->get(...)` anywhere.
- Constructor injects `CsrfTokenManagerInterface` — not fetched from the container.
- First executable statement of the action body IS the CSRF check.
- `throw` on `createAccessDeniedException()` — no silent no-op.

---

## 17. Pre-completion checklist

Before ticking any IMPLEMENTATION_PLAN.md step that touches a controller:

- [ ] DTO exists in `src/<Bundle>/Dto/<Feature>/`, is `final`, typed public properties, Assert attributes.
- [ ] No DTO property is a bag type (`ParameterBag` / `InputBag` / `HeaderBag` / `FileBag` / `ServerBag` / `Request`).
- [ ] Every embedded / nested DTO lives under `src/<Bundle>/Dto/Embedded/`, is flat, bundle-local, reused ≥ 2× (or has a documented single-use reason).
- [ ] Parent DTO properties carrying an embedded DTO carry `#[Assert\Valid]`.
- [ ] Parent DTO has a small `__construct()` that news-up embedded DTO instances (denormaliser needs live objects).
- [ ] Every consolidated `<Resource>SaveRequest` uses Symfony validation groups (`groups: ['insert']` / `groups: ['update']` / `groups: ['register']`) when Insert / Update / Register Assert profiles diverge; controllers pass `#[MapRequestPayload(..., validationGroups: ['Default', '<group>'])]` on the corresponding endpoints.
- [ ] File uploads are typed `UploadedFile` / `?UploadedFile` / `array<int, UploadedFile>` DTO properties with `Assert\Image` / `Assert\File` constraints and (for collections) `Assert\Count`.
- [ ] Controller signature carries `#[MapRequestPayload]` (JSON), `#[MapRequestPayload(acceptFormat: 'form')]` (form / multipart), or `#[MapQueryString]` (GET filter). DELETE actions carry no mapping attribute.
- [ ] CSRF DTO property is exactly `string $csrfToken` with `#[Assert\NotBlank]` and `#[SerializedName(<wire_key>)]` — nothing else CSRF-related.
- [ ] Controller-side CSRF check is the FIRST executable statement of the action body, uses `$this->csrfTokenManager->isTokenValid(new CsrfToken(<intention>, $dto->csrfToken))`, and uses a real `throw`.
- [ ] No `$request->request->get`, `$request->query->get`, `$request->files->get`, `$request->headers->get`, `$request->cookies->get`, `$request->getContent()` anywhere in the migrated action's file.
- [ ] Parser exists (survives from the pre-migration codebase), signature `parse(<Dto> $dto[, ?<Entity> $entity = null]): <Entity>` (or `: <DomainValueObject>` for degenerate cases). No raw request in the Parser.
- [ ] Failure-shape formatter registered in `SiteBundle\EventListeners\PayloadMappingFailureListener::$formatters` (per-route) if the endpoint has a non-default failure JSON shape.
- [ ] Every referenced class imported via `use` at the top of the file. No inline FQCN. No dynamic class names.
- [ ] Yoda conditions used (`false === $x`).
- [ ] Cache clear passes (`docker exec smestaj-app php bin/console cache:clear --env=dev` and `--env=prod`).
- [ ] If a route was added/changed: ran `docker exec smestaj-app php bin/console fos:js-routing:dump` and `bazinga:js-translation:dump` (per AGENTS.md).

---

## 18. Anti-patterns — do NOT do

- Controller signature `public function create(Request $request)` reading the body. Request is forbidden as the data input for POST/PUT/PATCH/GET-with-query — DTO only. `Request $request` remains legitimate ONLY for `DELETE` slim-CSRF resolvers.
- `$entity->setX($request->request->get('x'))` in the controller. That belongs in the Parser.
- `$request->files->get('photo')` or `$request->files->all('images')` anywhere in a controller. Files belong on the DTO as `UploadedFile` properties.
- Passing files as a separate Parser argument (`parser->parse($dto, $files, $entity)`). Files live on the DTO.
- Skipping `Assert\Image` / `Assert\File` on `UploadedFile` properties — you have an upload-anything endpoint.
- `return $this->json(['id' => $entity->getId(), 'title' => $entity->getTitle()])` — bypasses the View. Use `return new JsonResponse($this->view->view($entity))`.
- `if ('' === $dto->title) { return new JsonResponse(['error' => …], 422); }` in the controller. That is what `#[Assert\NotBlank]` on the DTO is for.
- `try/catch (ValidationFailedException)` to convert MapRequestPayload's automatic exception into a custom shape — let the framework raise it and let the kernel listener rewrite the body.
- `try/catch (HttpException)` inside a migrated controller to shape a mapping/denormalization failure — same rule, listener owns it.
- Custom `#[Assert\CsrfToken]` / `#[ValidCsrfToken]` / `#[CsrfValid]` — the DTO holds a plain `string $csrfToken` only.
- Custom `ConstraintValidator` for CSRF — verification is one line in the controller.
- Pre-controller listener for CSRF verification — verification is one line in the controller.
- `$this->createAccessDeniedException();` without `throw` — silent no-op; always `throw $this->createAccessDeniedException();`.
- `#[MapQueryString]` on a `DELETE` action — spec-forbidden even for CSRF-only DTOs.
- Creating a file under `src/SiteBundle/Validators/` (plural-s) — that is legacy. Use `Validator/` (singular).
- **Duplicating** a `<Feature>InsertRequest` + `<Feature>UpdateRequest` pair whose property lists are identical or near-identical. Consolidate to a single `<Feature>SaveRequest`. If Assert profiles diverge, use validation groups (`groups: ['insert']`); if they match, one Assert set runs on both endpoints.
- **Extracting** an embedded DTO for a **flat** wire cluster (e.g. `lat`/`lng` on a flat form). Embedded DTOs are only for genuinely-nested wire shapes (bracket-notation sub-trees, JSON arrays of objects, JSON sub-objects) — see §10.
- Duplicating an existing View — if `SiteBundle\View\CategoryView` exists, AdminBundle reuses it.
- Constructor promotion on an embedded DTO — clashes with the denormaliser's property-access path.

---

## 19. When to load this skill

Load with `skill({ name: "controller-pattern" })` **before** writing or editing ANY file under:

- `src/AdminBundle/Controller/`, `src/SiteBundle/Controller/`, `src/LogBundle/Controller/`.
- `src/<Bundle>/Dto/`, `src/<Bundle>/Parser/`, `src/<Bundle>/Validator/`, `src/<Bundle>/View/`.
- `src/<Bundle>/Dto/Embedded/`.
- `src/SiteBundle/EventListeners/PayloadMappingFailureListener.php`.
- `src/<Bundle>/ValueResolver/`.

If you started a controller change without loading the skill, stop, load it now, and bring the already-written code into compliance before continuing.

---

## 20. Cited Symfony types (appendix)

- `Symfony\Component\HttpKernel\Attribute\MapRequestPayload`
- `Symfony\Component\HttpKernel\Attribute\MapQueryString`
- `Symfony\Component\HttpKernel\Attribute\ValueResolver`
- `Symfony\Component\HttpKernel\Attribute\AsEventListener`
- `Symfony\Component\HttpKernel\Controller\ValueResolverInterface`
- `Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestPayloadValueResolver` (default resolver, do NOT override)
- `Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata`
- `Symfony\Component\HttpKernel\Event\ExceptionEvent`
- `Symfony\Component\HttpKernel\Exception\HttpException`
- `Symfony\Component\HttpFoundation\File\UploadedFile`
- `Symfony\Component\HttpFoundation\JsonResponse`
- `Symfony\Component\HttpFoundation\Request` (legitimate ONLY in value resolvers)
- `Symfony\Component\Serializer\Attribute\SerializedName`
- `Symfony\Component\Security\Csrf\CsrfToken`
- `Symfony\Component\Security\Csrf\CsrfTokenManagerInterface`
- `Symfony\Component\Validator\Constraints as Assert`
- `Symfony\Component\Validator\Exception\ValidationFailedException`
- `Symfony\Bridge\Doctrine\Attribute\MapEntity`

Docs: <https://symfony.com/doc/current/controller.html#automatic-mapping-of-the-request>.

<?php

namespace Database\Factories;

use App\Domains\Task\Enums\TaskPriority;
use App\Domains\Task\Enums\TaskStatus;
use App\Domains\Task\Models\TaskModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskModel>
 */
class TaskModelFactory extends Factory
{
    protected $model = TaskModel::class;

    public function definition(): array
    {
        $sequence = fake()->unique()->numberBetween(1, 99999);

        $description = <<<'EOT'
# Fama misit
![](https://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Test-Logo.svg/960px-Test-Logo.svg.png?_=20150906031702)

## Verus defendite tibi penetratque

```mermaid
sequenceDiagram
    participant ZR as Zoho Recruit
    participant Sync as Application sync
    participant App as ApplicationModel
    participant Obs as ApplicationObserver
    participant Job as ConvertCandidateToContractorJob
    participant H as ConvertCandidateToContractorHandler
    participant Hire as HireContractorHandler
    participant CRM as Zoho CRM

    ZR->>Sync: Application stage updated
    Sync->>App: upsert
    App->>Obs: created / updated
    Obs->>Obs: wasChanged hiring_pipeline / application_status?
    Obs->>Obs: triggersConversion()
    Obs->>Job: dispatch (delay 5s, unique by application.id)
    Job->>H: handle(application)
    H->>H: resolveCandidateOrFail + loadMissing(applications)
    H->>H: resolveContractor (find or build)
    H->>Hire: handle(candidate, contractor)
    Hire->>Hire: resolver.resolve(candidate)
    Hire->>Hire: strategy.apply(candidate, contractor)
    Hire->>CRM: pushToCrmAndSync
    CRM-->>Hire: refreshed Contractor
```

Lorem markdownum quotiens `variable` regna inque, illo, stellae et, pauca!
Sputantem sibila nova arma, nutrimen in narres paenituisse gesserunt remisit
hosne resumere. Et parva `bannerSsh` signa rogat, e aevum guttis, mensis
canaeque cognoscere Ceres Iuppiter metuque Aeson est; incitat. Ad tendo at
urguet Palatia in ipso bracchia, confodit ter!

1. Nam citra nunc cum vos
2. Contraxit tempus
3. Velit reticere apertos valuere prole temptent hi
4. Quod vult levis audita Ergo incenduntque infelix
5. Tamen adamante et regia dum res et
6. Iuncta ab avidamque vacuo de et et

## Erat induta erit populis

Relicto cingentibus tectus! Ea aciem, habebat illuc manet terras sed tum, ima
pullo infelix quaerit. Virgineos tantum oras; est totum trux Cinyrae voce
Busirin ad. Marte cur ne virginibus dives, quoque erat Stymphalides ignibus
[oleis](#mox-ego-aeneaeque). Vultu amanti *per non natarum* hoc utque Troades
iudicium fiant sociis, turba, non Orpheus tamen.

    dock *= pum_bus;
    jsonSectorModem(impact);
    if (ram) {
        rosettaMenu /= up_registry;
    }
    var hubTextAsp = functionOemMetal;
    refreshPppWeb(component_zone_dma + sectorPath(skin_digital),
            passiveScraping(ict_flood_of / fios_markup, sataDeprecated, white +
            ipv_browser_compact), netmask_golden.desktopAlertRaster.wimax_lte(
            httpRecord));

Damno addita vincis **parte operisque** morte emicuit, erat illo *Iovis ille
vela* imagine multis. Expellitur gelidas est femina, crista, nec nostra super,
ut. Accepit coniugis carceris saevumque iter fugit, corpusque ventre, adeant
*dicit Semelen crudelior*. Exhalat an *osse Est*, reposco resupinus loco cumque
**per suos** Teucer stabula cives postquam a ego? Haeret cupressu ergo stridore
impune timuere, est interceperit nympha.

## Mox ego Aeneaeque

Grata specus enim tricuspide animalia e infernum maxima: Pomona exarsit *cum*.
Ictus **pro et** ipse tergoque, perdes die Minyeides precor praetendens militis
moras.

- Orta nostro
- Est nunc aras has inmittitur sola Laiades
- Spectantis urbesque
- Sed dilapsa temeraria vaticinata viscera vocisque est
- Fugatas Crete

Navem victa ipsis quam officium avertitur lupos Phoebi currus ianua, sic?
Proceres auras, manu dum. Meo tigno, illo nepos conclamat. Tibi dum?
EOT;

        return [
            'key'             => 'TASK-'.$sequence,
            'sequence_number' => $sequence,
            'name'            => fake()->words(3, true),
            'description'     => $description,
            'priority'        => fake()->randomElement(TaskPriority::cases())->value,
            'status'          => fake()->randomElement(TaskStatus::cases())->value,
        ];
    }
}

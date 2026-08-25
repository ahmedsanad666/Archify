import {
    IconBolt,
    IconBuilding,
    IconBuildingArch,
    IconBrush,
    IconCompass,
    IconHammer,
    IconHome,
    IconLamp,
    IconLayout,
    IconLeaf,
    IconPaint,
    IconPencil,
    IconRuler,
    IconSofa,
    IconTools,
    IconTree,
    IconUsers,
    IconWorld,
} from '@tabler/icons-vue'
import names from './names.json'

/**
 * Component map for every name in names.json.
 * When adding an icon: append to names.json AND register the Tabler component here.
 */
const COMPONENT_BY_NAME = {
    compass: IconCompass,
    leaf: IconLeaf,
    ruler: IconRuler,
    users: IconUsers,
    sofa: IconSofa,
    tree: IconTree,
    building: IconBuilding,
    'building-arch': IconBuildingArch,
    home: IconHome,
    lamp: IconLamp,
    hammer: IconHammer,
    tools: IconTools,
    bolt: IconBolt,
    paint: IconPaint,
    brush: IconBrush,
    pencil: IconPencil,
    layout: IconLayout,
    world: IconWorld,
}

export const APP_ICON_NAMES = names

export const APP_ICON_COMPONENTS = COMPONENT_BY_NAME

/**
 * @returns {{ name: string, component: object }[]}
 */
export function appIconEntries() {
    return APP_ICON_NAMES.map((name) => ({
        name,
        component: COMPONENT_BY_NAME[name] ?? IconLayout,
    }))
}

/**
 * Resolve a Tabler component for a stored icon name.
 */
export function resolveAppIcon(name, fallback = IconLayout) {
    if (!name) {
        return fallback
    }

    return COMPONENT_BY_NAME[name] ?? fallback
}

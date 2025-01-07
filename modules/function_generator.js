/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

const SYMETRIC_PROBABILITY = 0.5;
const POINTS_IN_CHART = 400; //pocet bodu zvysi kvalitu grafu, ale nacitani trva dele

let currently_fractions = [false, false, false, false]; //true pro hodnoty D(f)/H(f), ktere je potreba vyjadrit jako zlomek
let fraction_values = [
  [0, 1],
  [0, 1],
  [0, 1],
  [0, 1],
]; //citatele a jmenovatele pro zlomky

/**
 * @param a prvni cislo.
 * @param b druhe cislo.
 * @returns nejvetsi spolecny delitel.
 */
function gcd(a, b) {
  return b === 0 ? a : gcd(b, a % b);
}

/**
 *
 * @param numerator citatel.
 * @param denominator jmenovatel.
 * @returns zlomek v zakladnim tvaru v Latexovem formatu, pripadne jako cele cislo, pokud je po zjednoduseni jmenovatel 1 (4/2 => 2).
 */
function simplify_fraction(numerator, denominator) {
  const divisor = gcd(numerator, denominator);
  numerator /= divisor;
  denominator /= divisor;

  if (denominator === 1) {
    return `${numerator}`;
  }

  if (denominator === -1) {
    return `${-numerator}`;
  }
  if (numerator < 0 && denominator < 0) {
    return `\\frac{${numerator * -1}}{${denominator * -1}}`;
  } else if (numerator * denominator < 0) {
    return `-\\frac{${Math.abs(numerator)}}{${Math.abs(denominator)}}`;
  }
  return `\\frac{${numerator}}{${denominator}}`;
}

/**
 * hledani maxima probiha takto:
 * - mame fci f(x) = a * sin(bx + c) + d
 * - maximem je budto:
 *   - f(x) = a+d: sin(bx + c) = 1
 *   - bx + c = π/2 + 2kπ
 *   - x = (π/2 + 2kπ - c)/b
 *   - to plati pro nasledujici k a interval <x_min, x_max>:
 *     - 2kπ = bx + c - π/2
 *     - k = (bx + c - π/2)/2π
 *     - k_min = ⌊(b*x_min + c - π/2)/2π⌋
 *     - k_max = ⌈(b*x_max + c - π/2)/2π⌉
 *    pokud k_max - k_min > 1, maximem je a+d (sin(bx+c) = 1)
 * - nebo, pokud takovy bod zadny neexistuje, je maximem:
 *   - max(f(x_min), f(x_max))
 * - podobne probiha i hledani minima
 * @param x_min zacatek intervalu definicniho oboru.
 * @param x_max konec intervalu definicniho oboru.
 * @param a parametr a zminene funkce.
 * @param b parametr b zminene funkce.
 * @param c parametr c zminene funkce.
 * @param d parametr d zminene funkce.
 * @param funct funkce.
 * @returns pole ve tvaru [<infimum H(f)>, <supremum H(f)>, <existence maxima H(f)>, <existence minima H(f)>].
 */
function getSinMinMax(x_min, x_max, a, b, c, d, funct) {
  let k_min = Math.floor((b * x_min + c - Math.PI / 2) / (2 * Math.PI));
  let k_max = Math.ceil((b * x_max + c - Math.PI / 2) / (2 * Math.PI));
  const top_peak_exists = Math.abs(k_max - k_min) > 1;
  const f_max = top_peak_exists ? a + d : Math.max(funct(x_min), funct(x_max));
  k_min = Math.floor((b * x_min + c - (3 * Math.PI) / 2) / (2 * Math.PI));
  k_max = Math.ceil((b * x_max + c - (3 * Math.PI) / 2) / (2 * Math.PI));
  const bottom_peak_exists = Math.abs(k_max - k_min) > 1;
  const f_min = bottom_peak_exists
    ? d - a
    : Math.min(funct(x_min), funct(x_max));
  return [f_min, f_max, top_peak_exists, bottom_peak_exists];
}

/**
 * Na strance vykresli graf predane funkce a informace o nem.
 * @param thatFunction funkce, kterou chceme vykreslit.
 * @param chartId id grafu, musi byt jedinecne.
 * @param interval definicni obor.
 * @param symetry obsahuje informaci o tom, jestli je funkce suda/licha/nesymetricka.
 * @param yAxis obor hodnot.
 */
function create_chart(thatFunction, chartId, interval, symetry, yAxis) {
  let xValues = [];
  let yValues = [];

  /*
  0: nezacalo se vykreslovat
  1: nemonotonni
  2: rostouci
  3: klesajici
  4: nerostouci a neklesajici
  5: nerostouci
  6: neklesajici
  */
  let monotony = 0;
  let lastFx = null;

  for (
    let x = interval.start.x;
    x <= interval.end.x;
    x += (interval.end.x - interval.start.x) / POINTS_IN_CHART
  ) {
    let fx = thatFunction(x);
    if (monotony != 1 && lastFx != null) {
      if (fx > lastFx) {
        if (monotony == 0) monotony = 2;
        else if (monotony == 3 || monotony == 5) {
          monotony = 1;
        } else if (monotony == 4) {
          monotony = 6;
        }
      } else if (fx < lastFx) {
        if (monotony == 0) monotony = 3;
        else if (monotony == 2 || monotony == 6) {
          monotony = 1;
        } else if (monotony == 4) {
          monotony = 5;
        }
      } else {
        if (monotony == 0) monotony = 4;
        else if (monotony == 2) {
          monotony = 6;
        } else if (monotony == 3) {
          monotony = 5;
        }
      }
    }
    xValues.push(x);
    yValues.push(fx);
    lastFx = fx;
  }

  // Data pro graf
  const data = [
    {
      x: xValues,
      y: yValues,
      type: "scatter",
      hoverinfo: "skip",
      mode: "lines",
    },
    {
      x: [interval.start.x],
      y: [thatFunction(interval.start.x)],
      mode: "markers",
      type: "scatter",
      hoverinfo: "skip",
      marker: {
        color: interval.start.open ? "white" : "blue",
        line: interval.start.open
          ? {
              color: "blue",
              width: 1,
            }
          : {},
      },
    },
    {
      x: [interval.end.x],
      y: [thatFunction(interval.end.x)],
      mode: "markers",
      type: "scatter",
      hoverinfo: "skip",
      marker: {
        color: interval.end.open ? "white" : "blue",
        line: interval.end.open
          ? {
              color: "blue",
              width: 1,
            }
          : {},
      },
    },
  ];

  const yDelta = yAxis.max - yAxis.min;

  let between = [];
  if (yDelta < 7) {
    for (let i = 1; i < yDelta; i++) {
      between.push(yAxis.min + i);
    }
  } else {
    let gap = parseInt(yDelta / 5);
    for (let i = gap; i < yDelta; i += gap) {
      between.push(yAxis.min + i);
    }
  }

  // Vykresleni grafu
  Plotly.newPlot("myChart" + chartId, data, {
    title: "Graf funkce",
    showlegend: false,
    xaxis: {
      title: "x",
      tickmode: "linear",
      tick0: interval.start.x,
      dtick: 1,
    },
    yaxis: {
      title: "y",
      tickvals: [yAxis.min, ...between, yAxis.max],
    },
  });

  document.getElementById("solution" + chartId).innerHTML =
    "Řešení: D(f): \\(" +
    (interval.start.open ? "(" : "<") +
    (currently_fractions[2]
      ? simplify_fraction(fraction_values[2][0], fraction_values[2][1])
      : interval.start.x) +
    "; " +
    (currently_fractions[3]
      ? simplify_fraction(fraction_values[3][0], fraction_values[3][1])
      : interval.end.x) +
    (interval.end.open ? "), " : ">, ") +
    "\\)H(f): \\(" +
    (yAxis.bottomPeak
      ? "<"
      : thatFunction(interval.start.x) < thatFunction(interval.end.x)
      ? interval.start.open
        ? "("
        : "<"
      : interval.end.open
      ? "("
      : "<") +
    (currently_fractions[0]
      ? simplify_fraction(fraction_values[0][0], fraction_values[0][1])
      : yAxis.min) +
    "; " +
    (currently_fractions[1]
      ? simplify_fraction(fraction_values[1][0], fraction_values[1][1])
      : yAxis.max) +
    (yAxis.topPeak
      ? ">"
      : thatFunction(interval.start.x) > thatFunction(interval.end.x)
      ? interval.start.open
        ? ")"
        : ">"
      : interval.end.open
      ? ")"
      : ">") +
    "\\), " +
    [
      "nemonotónní",
      "rostoucí",
      "klesající",
      "nerostoucí a neklesající",
      "nerostoucí",
      "neklesající",
    ][monotony - 1] +
    ", " +
    (symetry.even ? "sudá" : symetry.odd ? "lichá" : "není symetrická") +
    ", " +
    /* plati jen za predpokladu spojite fce */
    (monotony != 2 && monotony != 3 ? "není prostá" : "prostá");
}

/**
 * Vygeneruje sinusoidu.
 * @param isSymetric true, pokud ma byt vysledek symetricky.
 * @param {*} interval definicni obor.
 * @returns standardizovane informace o vygenerovane funkci.
 */
function getSinFunction(isSymetric, interval) {
  const rand = Math.random();
  const param1 = Math.random() > 0.5 ? 1 : 2;
  const param2 = parseInt(Math.random() * 19 + 1) / 10;
  const param3 = isSymetric
    ? rand < 0.5
      ? 0
      : rand < 0.75
      ? Math.PI / 2
      : -Math.PI / 2
    : parseInt(Math.random() * 20) / 2 - 5;
  const param4 = isSymetric ? 0 : parseInt(Math.random() * 20) / 2 - 5;

  const param3ModPeriod = param3 % (2 * Math.PI);
  const delta = 0.2;

  if (
    !isSymetric &&
    interval.start.x == interval.end.x &&
    interval.start.open == interval.end.open &&
    (Math.abs(param3ModPeriod) < delta ||
      Math.abs(param3ModPeriod - Math.PI) < delta ||
      Math.abs(param3ModPeriod + Math.PI) < delta) &&
    param4 == 0
  ) {
    //problem (vygenerovali jsme symetrickou fci, prestoze nema byt symetricka)
    return getSinFunction(isSymetric, interval);
  }

  const funct = function (x) {
    return param1 * Math.sin(param2 * x + param3) + param4;
  };
  let minMax = getSinMinMax(
    interval.start.x,
    interval.end.x,
    param1,
    param2,
    param3,
    param4,
    funct
  );

  return {
    f: funct,
    symetry: {
      odd: isSymetric && rand < 0.5 ? true : false,
      even: isSymetric && rand >= 0.5 ? true : false,
    },
    y: {
      min: minMax[0],
      max: minMax[1],
      topPeak: minMax[2],
      bottomPeak: minMax[3],
    },
  };
}

/**
 * Vygeneruje linearni funkci.
 * @param {*} isSymetric true, pokud vysledek ma byt symetricky.
 * @param {*} interval definicni obor.
 * @returns standardizovane informace o vygenerovane funkci.
 */
function getLinearFunction(isSymetric, interval) {
  const param1 =
    parseInt(Math.random() * 4 + 1) * (Math.random() < 0.5 ? -1 : 1);
  const param2 = isSymetric ? 0 : parseInt(Math.random() * 10) - 5;
  const funct = function (x) {
    return param1 * x + param2;
  };
  const min = Math.min(funct(interval.start.x), funct(interval.end.x));
  const max = Math.max(funct(interval.start.x), funct(interval.end.x));

  return {
    f: funct,
    symetry: {
      odd: false,
      even: isSymetric,
    },
    y: {
      min,
      max,
      topPeak:
        max == interval.start.x ? !interval.start.open : !interval.end.open,
      bottomPeak:
        min == interval.start.x ? !interval.start.open : !interval.end.open,
    },
  };
}

/**
 * Vygeneruje jednu z dalsich znamych funkci: x**2, x**3, log(x), upravena sign(x), aby byla spojita.
 * @param {*} isSymetric true, pokud vysledek ma byt symetricky.
 * @param {*} interval definicni obor.
 * @returns standardizovane informace o vygenerovane funkci.
 */
function getOtherWellKnownFunction(isSymetric, interval) {
  if (isSymetric) {
    const r = Math.random() < 0.5 ? 2 : 3;
    const funct = function (x) {
      return x ** r;
    };

    const startValue = funct(interval.start.x);
    const endValue = funct(interval.end.x);

    let min, max;
    if (r == 2 && interval.start.x <= 0 && interval.end.x >= 0) {
      min = 0;
      max = Math.max(startValue, endValue);
    } else {
      min = Math.min(startValue, endValue);
      max = Math.max(startValue, endValue);
    }

    return {
      f: funct,
      symetry: {
        odd: r == 3 && isSymetric ? true : false,
        even: r == 2 && isSymetric ? true : false,
      },
      y: {
        min,
        max,
        topPeak:
          max == interval.start.x ? !interval.start.open : !interval.end.open,
        bottomPeak:
          min == interval.start.x ? !interval.start.open : !interval.end.open,
      },
    };
  } else if (interval.start.x > 0) {
    const min = Math.log(interval.start.x);
    const max = Math.log(interval.end.x);
    return {
      f: Math.log,
      symetry: {
        odd: false,
        even: false,
      },
      y: {
        min,
        max,
        topPeak:
          max == interval.start.x ? !interval.start.open : !interval.end.open,
        bottomPeak:
          min == interval.start.x ? !interval.start.open : !interval.end.open,
      },
    };
  } else {
    const funct = function (x) {
      if (Math.abs(x) < 1) {
        return x;
      }
      return Math.sign(x);
    };
    const min = funct(interval.start.x);
    const max = funct(interval.end.x);
    return {
      f: funct,
      symetry: {
        odd: false,
        even: isSymetric,
      },
      y: {
        min,
        max,
        topPeak:
          max == interval.start.x ? !interval.start.open : !interval.end.open,
        bottomPeak:
          min == interval.start.x ? !interval.start.open : !interval.end.open,
      },
    };
  }
}

/**
 * Vygeneruje spojitou funkci slozenou z vice usecek.
 * @param {*} isSymetric true, pokud vysledek ma byt symetricky.
 * @param {*} interval definicni obor.
 * @returns standardizovane informace o vygenerovane funkci.
 */
function getLinesFunction(isSymetric, interval) {
  if (isSymetric) {
    const param1 =
      parseInt(Math.random() * 4 + 1) * (Math.random() < 0.5 ? -1 : 1);
    const param2 =
      parseInt(Math.random() * 4 + 1) * (Math.random() < 0.5 ? -1 : 1);
    const funct = function (x) {
      if (x < 0) {
        return param1 * x + param2;
      } else return -1 * param1 * x + param2;
    };
    const min = Math.min(funct(interval.start.x), param2);
    const max = Math.max(funct(interval.start.x), param2);
    return {
      f: funct,
      symetry: {
        odd: false,
        even: true,
      },
      y: {
        min,
        max,
        topPeak:
          max == interval.start.x ? !interval.start.open : !interval.end.open,
        bottomPeak:
          min == interval.start.x ? !interval.start.open : !interval.end.open,
      },
    };
  } else {
    const totalLines = Math.random() < 0.5 ? 2 : 3;
    let lines = [];
    let lastShift = null;
    for (let i = 0; i < totalLines; i++) {
      const multiplier =
        parseInt(Math.random() * 4 + 1) * (Math.random() < 0.5 ? -1 : 1);
      let shift =
        parseInt(Math.random() * 4 + 1) * (Math.random() < 0.5 ? -1 : 1);
      if (lastShift != null) {
        //aby byl graf spojity
        const startX =
          interval.start.x +
          ((interval.end.x - interval.start.x) * i) / totalLines;
        let lastLineEnd = lines[i - 1][0] * startX + lines[i - 1][1];
        /* 
        lastLineEnd = multiplier*startX+shift
        =>
        shift = lastLineEnd - multiplier*startX
        */
        shift = lastLineEnd - multiplier * startX;
      }
      lastShift = shift;
      lines.push([multiplier, shift]);
    }
    const funct = function (x) {
      let index = parseInt(
        ((x - interval.start.x) / (interval.end.x - interval.start.x)) *
          totalLines -
          0.0001
      );
      return lines[index][0] * x + lines[index][1];
    };

    let values = [];
    for (let i = 0; i < totalLines; i++) {
      values.push(
        lines[i][0] *
          (((interval.end.x - interval.start.x) * i) / totalLines +
            interval.start.x) +
          lines[i][1]
      );
    }
    values.push(
      lines[lines.length - 1][0] * interval.end.x + lines[lines.length - 1][1]
    );
    const min = Math.min(...values);
    const max = Math.max(...values);

    return {
      f: funct,
      symetry: {
        odd: false,
        even: false,
      },
      y: {
        min,
        max,
        topPeak:
          max == interval.start.x ? !interval.start.open : !interval.end.open,
        bottomPeak:
          min == interval.start.x ? !interval.start.open : !interval.end.open,
      },
    };
  }
}

/**
 * Zkontroluje, jestli vystupy funkce odpovidaji pozadavkum uzivatele. Z vykonostnich duvodu pri urcitych typech problemu radsi
 * funkci poupravi aby odpovidala pozadavkum, nez aby vynutil generovani nove funkce.
 * @param fun_data veskera data, ktere mame o funkci dostupna z doby jejiho generovani.
 * @param x_interval definicni obor.
 * @param do_not_call_recursively true, pokud nechceme jiz znovu zkouset upravit funkci a prekontrolovat, ale chceme v pripade neuspechu
 * rovnou vratit false.
 * @returns true, pokud je funkce v poradku, jinak false.
 */
function good_output(fun_data, x_interval, do_not_call_recursively = false) {
  let y = fun_data.y;
  let depends_of_fraction_seen = false;
  let fraction_seen = false;
  let random_seen = false;
  let i = -1;
  for (let num of [y.min, y.max, x_interval.start.x, x_interval.end.x]) {
    i++;
    let type_now;
    if (num == parseInt(num)) {
      currently_fractions[i] = false;
      type_now = "integer";
      if (
        output_types.includes("fraction") &&
        !output_types.includes("integer")
      ) {
        depends_of_fraction_seen = true;
        continue;
      }
    } else if (num * 100 == parseInt(num * 100)) {
      currently_fractions[i] = true;
      fraction_values[i] = [num * 100, 100];
      type_now = "fraction";
      fraction_seen = true;
    } else {
      currently_fractions[i] = false;
      type_now = "random";
      random_seen = true;
    }
    if (!output_types.includes(type_now) && !output_types.includes("random")) {
      return false;
    }
  }
  if (output_types.includes("random") && output_types.length == 1) {
    return random_seen;
  }
  if (depends_of_fraction_seen) {
    if (fraction_seen) return true;
    if (do_not_call_recursively) return false;
    let denominator = parseInt(Math.random() * 10);
    let original_f = fun_data.f;
    fun_data.f = function (x) {
      return original_f(x) / denominator;
    };
    currently_fractions = [true, true, false, false];
    fraction_values = [
      [y.min, denominator],
      [y.max, denominator],
      [0, 1],
      [0, 1],
    ];
    fun_data.y.min /= denominator;
    fun_data.y.max /= denominator;
    return good_output(fun_data, x_interval, true);
  }
  return true;
}

/**
 * @param isSymetric true, pokud vygenerovana funkce ma byt symetricka, jinak false.
 * @param interval definicni obor.
 * @returns standardizovane informace o nahodne vygenerovane funkci.
 */
function getFunction(isSymetric, interval) {
  const r = Math.random();
  if (r < 0.3) return getSinFunction(isSymetric, interval);
  else if (r < 0.45) return getLinearFunction(isSymetric, interval);
  else if (r < 0.75) return getOtherWellKnownFunction(isSymetric, interval);
  else return getLinesFunction(isSymetric, interval);
}

for (let i = 0; i < number_of_charts; i++) {
  let startX = parseInt(Math.random() * 10);
  let interval = {
    start: {
      x: startX,
      open: Math.random() < 0.5,
    },
    end: {
      x: startX + parseInt(Math.random() * 10) + 1,
      open: Math.random() < 0.5,
    },
  };
  let isSymetric = Math.random() < SYMETRIC_PROBABILITY;
  if (isSymetric) {
    //symetricka funkce musi mit D(f) symetricky kolem pocatku
    interval.end.x = parseInt(Math.random() * 5) + 1;
    interval.start.x = -interval.end.x;
    interval.start.open = interval.end.open;
  }
  let fun_data;
  do {
    fun_data = getFunction(isSymetric, interval);
  } while (!good_output(fun_data, interval));
  create_chart(fun_data.f, i, interval, fun_data.symetry, fun_data.y);
}

<?php

namespace PC\Enums;

enum SSLModes:string {
    case Disable = "disable";
    case Allow = "allow";
    case Prefer = "prefer";
    case Require = "require";
    case VerifyCA = "verify-ca";
    case VerifyFull = "verify-full";
}
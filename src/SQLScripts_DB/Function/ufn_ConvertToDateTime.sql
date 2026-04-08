USE [BBCSchedules]
GO
/****** Object:  UserDefinedFunction [dbo].[ufn_ConvertToDateTime]    Script Date: 10/07/2025 12:50:00 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER      FUNCTION [dbo].[ufn_ConvertToDateTime]
 (	
	@DutyDate			DATE,
	@TimeInSec			INT
 )
RETURNS DATETIME
AS
BEGIN

   DECLARE @ConvertedDateTime DATETIME;

	SELECT @ConvertedDateTime = cast( Concat(Format ( @DutyDate, 'yyyy-MM-dd'), ' ' , (
									RIGHT('0' + Cast(Cast(ISNULL(CASE WHEN @TimeInSec >= 86400 THEN @TimeInSec  - 86400 ELSE @TimeInSec END,0) AS
									INT) / 3600 AS VARCHAR ), 2)
									+ ':'
									+ RIGHT('0' + Cast((Cast(ISNULL(CASE WHEN @TimeInSec >= 86400 THEN @TimeInSec  - 86400 ELSE @TimeInSec END,0)
									AS INT) / 60) % 60 AS VARCHAR), 2)
									+ ':'
									+ RIGHT('0' + Cast(Cast(ISNULL(CASE WHEN @TimeInSec >= 86400 THEN @TimeInSec  - 86400 ELSE @TimeInSec END,0)
									AS INT) % 60 AS VARCHAR ), 2)
									+ '.000' )) as datetime) 

	RETURN @ConvertedDateTime

END
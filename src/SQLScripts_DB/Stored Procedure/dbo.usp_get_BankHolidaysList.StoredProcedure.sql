USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_BankHolidaysList]    Script Date: 23/03/2023 19:18:05 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_get_BankHolidaysList]
@startDate VARCHAR(22),
@EndDate VARCHAR(22)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	
	SET NOCOUNT ON;

	SELECT dDateTime,sEvent
	FROM TimeDimension
	WHERE dDateTime BETWEEN CONVERT(DATETIME,@startDate,101) AND CONVERT(DATETIME,@EndDate,101)
	AND fHolidayFlag = 1
END
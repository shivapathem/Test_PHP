USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_SystestemDownTime]    Script Date: 25/07/2025 23:27:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE OR ALTER PROCEDURE [dbo].[usp_SystestemDownTime]
	@casevar varchar(10),
	@rowID int,
	@strStartTime varchar(25),
	@strEndTime varchar(25)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	IF(@casevar='FETCH')
	BEGIN
		SELECT StartTime, EndTime FROM DownTime;
	END
	IF(@casevar='DELETE')
	BEGIN
		DELETE FROM DownTime
	END

	IF(@casevar='INSERT')
	BEGIN
		INSERT INTO DownTime (StartTime, EndTime) VALUES (CONVERT(DATETIME, @strStartTime, 102),CONVERT(DATETIME, @strEndTime, 102));
	END

	IF(@casevar='UPDATE')
	BEGIN
		UPDATE DownTime SET StartTime =CONVERT(DATETIME, @strStartTime, 102), EndTime=CONVERT(DATETIME, @strEndTime, 102); 
	END
	
END
USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_NextPRevWeekNoByWeekNo]    Script Date: 27/10/2022 17:53:00 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE OR ALTER    PROCEDURE [dbo].[usp_get_NextPRevWeekNoByWeekNo] 
	-- Add the parameters for the stored procedure here
	@weekno int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @nextweek INT,@prevweek INT,@nextyear INT,@nextweekno INT, @prevyear INT,@prevweekno int
	DECLARE @curretnweek int ,@currentyear INT,@currentweekno INT
    -- Select statements for procedure here
	SELECT TOP 1 @nextweek =ixYearWeek,@nextyear=ixYear,@nextweekno =ixWeekInYear  from TimeDimension (nolock) where ixYearWeek > @weekno  order by ixYearWeek ASC
	
	SELECT TOP 1 @prevweek= ixYearWeek,@prevyear=ixYear,@prevweekno =ixWeekInYear from TimeDimension (nolock) where ixYearWeek < @weekno  order by ixYearWeek DESC
	
	SELECT TOP 1 @curretnweek =ixYearWeek,@currentyear=ixYear,@currentweekno =ixWeekInYear  from TimeDimension (nolock) where ixYearWeek= @weekno  order by ixYearWeek asc


	Select @prevyear PrevYear, @prevweekno PrevWeekNo,@prevweek PrevWeek,@currentyear CurrentYear,@currentweekno CurrentWeekNo,@curretnweek CurrentWeek,@nextyear NextYear,@nextweekno NextWeekNo,@nextweek NextWeek 

END

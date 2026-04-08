-- ================================================
-- Template generated from Template Explorer using:
-- Create Procedure (New Menu).SQL
--
-- Use the Specify Values for Template Parameters 
-- command (Ctrl-Shift-M) to fill in the parameter 
-- values below.
--
-- This block of comments will not be included in
-- the definition of the procedure.
-- ================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE OR ALTER PROCEDURE [dbo].[USP_INSERT_AND_UPDATE_REQUEST_TYPE]
	-- Add the parameters for the stored procedure here
	@ID int,@description nvarchar(max),@sdate datetime,@edate datetime, @day0 int, @day1 int, @day2 int,@day3 int, @day4 int, @day5 int, @day6 int,@intGroupID int, @isRestricted int, @UniqueCount int,@NumberAllowed int, @starts int, @AllowOverLimit int, @affectlocks int, @AffectsOthers int,  @ends int,@intSendEmails int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    IF(@ID=0)
	BEGIN
	INSERT INTO RequestTypes(
                description,
                startdate,
                enddate,
                day_0,
                day_1,
                day_2,
                day_3,
                day_4,
                day_5,
                day_6,
                GroupID,
                isRestricted,
                UniqueCount,
                RequestsAllowed,
                Starts,
                AllowOverLimit,
                AffectLocks,
                AffectsOthers,
                Ends, 
                SendEmails
                )
                VALUES (
                @description,
                CONVERT(DATETIME, @sdate, 102),
                CONVERT(DATETIME, @edate, 102),
                @day0,
                @day1,
                @day2,
                @day3,
                @day4,
                @day5,
                @day6,
                @intGroupID,
                @isRestricted,
                @UniqueCount,
                @NumberAllowed,
                @starts,
                @AllowOverLimit,
                @affectlocks,
                @AffectsOthers, 
                @ends,
                @intSendEmails
                )
	END
	ELSE 
	BEGIN
			UPDATE RequestTypes
                SET
                description = @description,
                startdate = CONVERT(DATETIME, @sdate, 102),
                enddate = CONVERT(DATETIME, @edate, 102),
                day_0 = @day0,
                day_1 = @day1,
                day_2 = @day2,
                day_3 = @day3,
                day_4 = @day4,
                day_5 = @day5,
                day_6 = @day6,
                GroupID = @intGroupID,
                isRestricted = @isRestricted,
                UniqueCount = @UniqueCount,
                RequestsAllowed = @NumberAllowed,
                Starts = @starts,
                Ends = @ends,
                AllowOverLimit = @AllowOverLimit,
                AffectLocks = @affectlocks,
                AffectsOthers = @AffectsOthers,
                SendEmails = @intSendEmails 
                WHERE (ID = @ID)
	END
END
GO
